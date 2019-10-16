<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 09.02.18
 * Time: 14:26
 */

namespace Office\Command;

use App\Entity\Setting;
use App\Service\DateTime;
use Auth\Entity\User;
use Cms\Service\SettingService;
use Doctrine\ORM\EntityManager;
use Office\Entity\Company;
use Office\Entity\Invoice;
use Office\Entity\InvoiceItem;
use Office\Entity\Kkt;
use Office\Entity\MoneyHistory;
use Office\Entity\Processing;
use Office\Entity\Shop;
use Office\Entity\Tariff;
use Office\Service\SendMail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;

/**
 * Class PaymentKktCommand
 *
 * @package Office\Command
 */
class PaymentKktCommand extends Command
{
    private $logger;

    private $entityManager;

    private $sendMail;

    /**
     * CheckUseKktCommand constructor.
     *
     * @param Logger $logger
     * @param EntityManager $entityManager
     * @param SendMail $sendMail
     */
    public function __construct(Logger $logger, EntityManager $entityManager, SendMail $sendMail)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->sendMail = $sendMail;

        parent::__construct();
    }

    /**
     * Configures the command
     */
    protected function configure(): void
    {
        $this
            ->setName('office:payment-kkt')
            ->setDescription('Оплата ККТ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkMaxTurnover();

        /** @var Kkt[] $kkts */
        $kkts = $this->entityManager->getRepository(Kkt::class)->createQueryBuilder('k')
            ->where('k.dateExpired <= :date and k.isDeleted = 0 AND k.fsVersion <> :fnVersion ')
            ->setParameter('date', new DateTime())
            ->setParameter('fnVersion', 'fn debug v 2.13')
            ->getQuery()->getResult();
        /** @var Tariff $defaultTariff */
        $defaultTariff = $this->entityManager->getRepository(Tariff::class)->findOneBy(['isDefault' => true]);

        if ($kkts) {
            $companyKktGroups = [];
            /** @var Kkt $kkt */
            foreach ($kkts as $kkt) {
                $users = $kkt->getShop()->getCompany()->getUser();
                $isSkip = false;
                foreach ($users as $u) {
                    if ($u->getId() === User::TEST_USER_ID) {
                        $isSkip = true;
                        break;
                    }
                }
                if ($isSkip) {
                    $this->logger->addDebug('Тестовая касса #'.$kkt->getSerialNumber().', пропускаем');
                    continue; //@TODO надо придумать что-то по нормаленей
                }

                $companyKktGroups[$kkt->getShop()->getCompany()->getId()][] = $kkt;
            }

            foreach ($companyKktGroups as $companyId => $companyKktGroup) {
                $tariffKktGroups = [];

                /** @var Kkt $companyKkt */
                foreach ($companyKktGroup as $companyKkt) {
                    $tariffKktGroups[$companyKkt->getTariff()->getId()][] = $companyKkt;
                }
                $companyPrice = 0;
                /** @var Kkt $tariffKkt */
                foreach ($tariffKktGroups as $tariffId => $tariffKktGroup) {
                    foreach ($tariffKktGroup as $tariffKkt) {
                        if ($tariffKkt->getTariffNext()) {
                            $companyPrice += $tariffKkt->getTariffNext()->getRentCost() * $tariffKkt->getTariffNext()->getMonthCount();
                            $tariffKkt->setTariff($tariffKkt->getTariffNext());
                            $tariffKkt->setTariffNext(null);
                            $tariffKkt->setTariffDateStart(new DateTime());
                            $this->entityManager->persist($tariffKkt);
                            $this->entityManager->flush();
                        } else {
                            $companyPrice += $tariffKkt->getTariff()->getRentCost() * $tariffKkt->getTariff()->getMonthCount();
                        }
                    }
                }

                /** @var Company $company */
                $company = $this->entityManager->getRepository(Company::class)->find($companyId);

                if ($company->getBalance() >= $companyPrice) {
                    $this->logger->addInfo('Оплата касс компании '.$company->getTitle());
                    $balance = $company->getBalance();
                    /** @var Kkt $companyKkt */
                    foreach ($companyKktGroup as $companyKkt) {
                        if ($companyKkt->getTariff()->getMonthLimit()) {
                            $limitDate = (new DateTime($companyKkt->getTariffDateStart()->format('Y-m-d')))
                                ->addMonths($companyKkt->getTariff()->getMonthLimit())
                                ->setTime(0, 0);
                            $currDate = (new DateTime())->setTime(0, 0);
                            if ($limitDate <= $currDate) {
                                //Списание оборотов если они есть
                                $monthTurnoverPercent = $this->entityManager->getRepository(Kkt::class)->getMonthTurnoverPercentKkt($companyKkt);
                                if (!empty($monthTurnoverPercent)) {
                                    $balance -= $monthTurnoverPercent;

                                    $history = new MoneyHistory();
                                    $history->setType(MoneyHistory::TYPE_OUT)
                                        ->setTitle('Списание по оборотам кассы #'.$companyKkt->getSerialNumber())
                                        ->setSum($monthTurnoverPercent)
                                        ->setDatetime(new \DateTime())
                                        ->setCompany($company);

                                    $this->entityManager->persist($history);
                                }
                                $companyKkt->setTariffNext($defaultTariff);
                            }
                        }

                        if ($companyKkt->getTariffNext() !== null) {
                            $companyKkt->setTariff($companyKkt->getTariffNext());
                            $companyKkt->setTariffNext(null);
                            $companyKkt->setTariffDateStart(new DateTime());
                        }

                        $monthCount = $companyKkt->getTariff()->getMonthCount();
                        $nextMonthPrice = $companyKkt->getTariff()->getRentCost() * $monthCount;

                        if ($nextMonthPrice <= $balance) {
                            $balance -= $nextMonthPrice;
                            $companyKkt->setDateExpired((new DateTime())->addMonths($monthCount));

                            $history = new MoneyHistory();
                            $history->setType(MoneyHistory::TYPE_OUT)
                                ->setTitle('Оплата кассы #'.$companyKkt->getSerialNumber())
                                ->setSum($nextMonthPrice)
                                ->setDatetime(new \DateTime())
                                ->setCompany($company);

                            $this->entityManager->persist($history);
                        } else {
                            $companyKkt->setDateExpired(new DateTime());
                            $this->logger->addDebug('Перевели на новый тариф, денег не хватило');
                            $this->notEnoughMoney($company, $nextMonthPrice, $companyKktGroup);
                        }
                    }

                    $company->setBalance($balance);
                    $this->entityManager->persist($company);

                    $this->entityManager->flush();
                    $this->logger->addInfo('Оплачено!');
                } else {
                    $this->notEnoughMoney($company, $companyPrice, $companyKktGroup);
                }
            }
        }

        $this->logger->info('Оплата ККТ', []);
    }

    /**
     * Проверка на максимальный оброт и списание за прошлый месяц
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    protected function checkMaxTurnover(): void
    {
        $isFirstDay = (new DateTime())->format('d') === '01';

        /** @var Company[] $companies */
        $companies = $this->entityManager->getRepository(Company::class)->findAll(['isDeleted' => false]);
        /** @var Tariff $tariffMain */
        $tariffMain = $this->entityManager->getRepository(Tariff::class)->findOneBy(['isDefault' => true]);
        $kktGroupMaxTurnover = [];
        $testuser = $this->entityManager->getRepository(User::class)->findOneBy(['id' => User::TEST_USER_ID]);
        foreach ($companies as $company) {
            if ($company->getCompanyEmail() != $testuser->getEmail()) {
                $kktUserGroupMaxTurnover = [];
                $balance = $company->getBalance();
                /** @var Shop[] $shops */
                $shops = $company->getShop();
                foreach ($shops as $shop) {
                    /** @var Kkt[] $kkts */
                    $kkts = $shop->getKkt()->filter(
                        function ($kkt) {
                            /** @var Kkt $kkt */
                            return (!empty($kkt->getTariff()) && $kkt->getTariff()->getMaxTurnover());
                        }
                    );
                    if (!empty($kkts)) {
                        //begin получить массив [код тарифа, [кассы по тарифу]]
                        $kktTariffsGroup = [];
                        foreach ($kkts as $kkt) {
                            $kktTariffsGroup[$kkt->getTariff()->getId()][] = $kkt;
                        }
                        //end получить массив [код тарифа, [кассы по тарифу]]

                        foreach ($kktTariffsGroup as $tariffId => $kktTariffGroup) {
                            /** @var Tariff $currTariff */
                            $currTariff = $this->entityManager->getRepository(Tariff::class)->findOneBy(['id' => $tariffId]);
                            $turnoverFromTariff = $currTariff->getMaxTurnover();
                            /** @var Kkt $currKkt */
                            foreach ($kktTariffGroup as $currKkt) {
                                //Получить оборот по чекам кассы
                                $kktTurnoverFromProcessing = $this->entityManager->getRepository(Kkt::class)->getMonthTurnoverKkt($currKkt);
                                if ($isFirstDay && !empty($currTariff->getTurnoverPercent())) {
                                    $turnover = $this->entityManager->getRepository(Processing::class)->getPrevMonthTurnoverKkt($currKkt);
                                    $this->logger->addDebug('Списание '.$turnover.' с кассы'.$currKkt->getSerialNumber());
                                    $turnoverPercent = $turnover / 100 * $currKkt->getTariff()->getTurnoverPercent();
                                    $balance -= $turnoverPercent;

                                    //begin выставление счета по % от оборота ежемесячно
                                    if (!empty($turnoverPercent) && $balance < 0) {
                                        $invoice = new Invoice();
                                        $invoice->setCompany($shop->getCompany())
                                            ->setUser($shop->getCompany()->getUser()[0])
                                            ->setDate(new DateTime())
                                            ->setStatus(0)
                                            ->setDateUpdate(new DateTime())
                                            ->setUpdater($shop->getCompany()->getUser()[0]);
                                        $invoiceItem = new InvoiceItem();
                                        $invoiceItem->setInvoice($invoice)
                                            ->setTitle('Тариф '.$currTariff->getTitle().' '.$currTariff->getTurnoverPercent().'% от оборота')
                                            ->setPrice($turnoverPercent)
                                            ->setTariff($currTariff)
                                            ->setQuantity(1)
                                            ->setSum($turnoverPercent);
                                        $this->entityManager->persist($invoiceItem);
                                        $sum = $invoiceItem->getSum();
                                        $invoice->setSum($sum);
                                        $this->entityManager->persist($invoice);

                                        //begin отправление письма о недостатке средств
                                        if ($currKkt->getFsVersion() !== Kkt::FN_DEBUG_VERSION) {
                                            $users = $company->getUser();
                                            $mails = [];
                                            foreach ($users as $u) {
                                                $mails[] = $u->getEmail();
                                            }
                                            $companyEmails = $this->entityManager->getRepository(Setting::class)
                                                ->findOneBy(['param' => SettingService::EMAIL_FOR_COMPANY])
                                                ->getValue();
                                            $mails = array_merge($mails, explode(',', $companyEmails));
                                            $this->sendMail->sendNoMoney($company, $mails);
                                            //end отправление письма о недостатке средств
                                        }
                                    }
                                    //end выставление счета по % от оборота ежемесячно

                                    $company->setBalance($balance);//Списать % от оборота по чекам кассы
                                }
                                if ($kktTurnoverFromProcessing > $turnoverFromTariff) {
                                    $this->logger->addWarning('Превышен максимальный оборот для кассы '.$currKkt->getId());
                                    $kktGroupMaxTurnover[$shop->getId()][] = clone $currKkt;
                                    $kktUserGroupMaxTurnover[$shop->getId()][] = clone $currKkt;
                                    //$Получить процент от оборота по чекам кассы
                                    $kktTurnoverPercent = $this->entityManager->getRepository(Kkt::class)
                                        ->getMonthTurnoverPercentKkt($currKkt);

                                    $balance -= $kktTurnoverPercent;

                                    //begin выставление счета по % от оборота при превышении лимита
                                    if (!empty($kktTurnoverPercent) && $balance < 0) {
                                        $invoice = new Invoice();
                                        $invoice->setCompany($shop->getCompany())
                                            ->setUser($shop->getCompany()->getUser()[0])
                                            ->setStatus(0)
                                            ->setDate(new DateTime())
                                            ->setDateUpdate(new DateTime())
                                            ->setUpdater($shop->getCompany()->getUser()[0]);
                                        $invoiceItem = new InvoiceItem();
                                        $invoiceItem->setInvoice($invoice)
                                            ->setTitle('Тариф '.$currTariff->getTitle().' '.$currTariff->getTurnoverPercent().'% от оборота')
                                            ->setPrice($kktTurnoverPercent)
                                            ->setTariff($currTariff)
                                            ->setQuantity(1)
                                            ->setSum($kktTurnoverPercent);
                                        $this->entityManager->persist($invoiceItem);
                                        $sum = $invoiceItem->getSum();
                                        $invoice->setSum($sum);
                                        $this->entityManager->persist($invoice);
                                    }
                                    //end выставление счета по % от оборота при превышении лимита

                                    $company->setBalance($balance);//Списать % от оборота по чекам кассы
                                    $currKkt->setDateExpired(new DateTime());//Сменить дату на текущую
                                    $currKkt->setTariff($tariffMain);//Перевести на основной тариф
                                    $currKkt->setTariffDateStart(new DateTime());
                                }
                                $this->entityManager->flush();
                            }
                        }
                    }
                }
                if (\count($kktUserGroupMaxTurnover)) {
                    $users = $company->getUser();
                    $mails = [];
                    foreach ($users as $u) {
                        $mails[] = $u->getEmail();
                    }
                    $this->sendMail->sendTariffMaxTurnover($kktUserGroupMaxTurnover, $mails);
                }
            }
        }
        if (\count($kktGroupMaxTurnover)) {
            $companyEmails = $this->entityManager->getRepository(Setting::class)
                ->findOneBy(['param' => SettingService::EMAIL_FOR_COMPANY])
                ->getValue();
            $mails = explode(',', $companyEmails);
            $this->sendMail->sendTariffMaxTurnover($kktGroupMaxTurnover, $mails);
        }

        $this->entityManager->flush();
    }

    /**
     * @param Company $company
     * @param float $companyPrice
     * @param $companyKktGroup
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    protected function notEnoughMoney(Company $company, float $companyPrice, $companyKktGroup)
    {
        /** @var Invoice $lastInvoice */
        $lastInvoice = $this->entityManager->getRepository(Invoice::class)->findOneBy(['company' => $company], ['dateUpdate' => 'DESC ']);
        $invoice = new Invoice();
        $invoice->setCompany($company)
            ->setStatus(0)
            ->setUser($company->getUser()[0])
            ->setDate(new DateTime())
            ->setDateUpdate(new DateTime())
            ->setUpdater($company->getUser()[0]);
        $invoice->setSum($companyPrice);

        $invoiceItem = new InvoiceItem();
        $invoiceItem->setInvoice($invoice)
            ->setTitle('Пополнение баланса')
            ->setPrice($companyPrice)
            ->setQuantity(1)
            ->setSum($companyPrice);

        $this->logger->addWarning('Недостаточно средств на счете компании '.$company->getTitle().' для оплаты '.\count($companyKktGroup));

        //begin отправление письма о недостатке средств
        $users = $company->getUser();
        $mails = [];
        foreach ($users as $u) {
            $mails[] = $u->getEmail();
        }
        $companyEmails = $this->entityManager->getRepository(Setting::class)->findOneBy(['param' => SettingService::EMAIL_FOR_COMPANY])->getValue();
        $mails = array_merge($mails, explode(',', $companyEmails));
        $this->sendMail->sendNoMoney($company, $mails);
        //end отправление письма о недостатке средств

        if (isset($lastInvoice)) {
            if ($lastInvoice->getSum() == $invoice->getSum() && !$lastInvoice->getStatus()) {
                return;
            }
        }
        $this->entityManager->persist($invoice);
        $this->entityManager->persist($invoiceItem);
        $this->entityManager->flush();
    }
}
