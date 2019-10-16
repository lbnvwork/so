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
use Office\Entity\Kkt;
use Office\Entity\Shop;
use Office\Service\SendMail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;
use Office\Entity\Tariff;

/**
 * Class CheckUserPaymentsCommand
 *
 * @package Office\Command
 */
class CheckUserPaymentsCommand extends Command
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
            ->setName('office:check-user-payments')
            ->setDescription('Проверка оплаты ККТ')
            ->addOption('not-send', 'ns');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkPaymentTerm();
        $this->logger->info('Проверка оплаты ККТ', []);
    }

    /**
     * @throws \Exception
     */
    protected function checkPaymentTerm()
    {
        /** @var Tariff[] $tariffs */
        $tariffs = $this->entityManager->getRepository(Tariff::class)->createQueryBuilder('t', 't.id')->getQuery()->getResult();
        /** @var Shop[] $shops */
        $shops = $this->entityManager->getRepository(Shop::class)->findAll(); //получить все магазины
        $shopsPaymentTerm = [];
        $companyPayment = [];

        foreach ($shops as $shop) {
            //begin получить кассы, где date = dateExpired - 7 дней
            /** @var Kkt[] $kkts */
            $kkts = $this->entityManager->getRepository(Kkt::class)->createQueryBuilder('k')
                ->where('k.dateExpired = :date AND k.shop=:shop AND k.dateExpired IS NOT NULL and k.isDeleted = 0')
                ->setParameter('date', (new DateTime())->setTime(00, 00, 00)->add(new \DateInterval('P7D')))
                ->setParameter('shop', $shop)
                ->getQuery()->getResult();
            //end получить кассы, где date = dateExpired - 7 дней
            if (!empty($kkts)) {
                $this->logger->addDebug('Обработка магазина '.$shop->getTitle());

                //begin получить массив [код тарифа, [кассы по тарифу]]
                $kktTariffs = [];
                foreach ($tariffs as $tariff) {
                    $kktTariffs[$tariff->getId()] = [];
                }
                foreach ($kkts as $kkt) {
                    $kktTariffs[$kkt->getTariff()->getId()][] = $kkt;
                }
                //end получить массив [код тарифа, [кассы по тарифу]]
                if (!isset($companyPayment[$shop->getCompany()->getId()])) {
                    $companyPayment[$shop->getCompany()->getId()]['price'] = 0;
                }
                $companyPayment[$shop->getCompany()->getId()]['shops'][$shop->getId()] = [];
                foreach ($kktTariffs as $id => $kktTariff) {
                    $tariff = $tariffs[$id];
                    if (!empty($kktTariff)) {
                        $turnover = $this->entityManager->getRepository(Shop::class)->getMonthTurnoverShop($shop);
                        //посчитать цену для каждой группы (по тарифу) в магазине
                        $turnoverPercent = $turnover / 100 * $tariff->getTurnoverPercent();
                        //посчитать цену для всех групп в магазине
                        $kktTariffPrice =
                            round(\count($kktTariff) * $tariff->getMonthCount() * ($tariff->getRentCost() + $turnoverPercent), 2);
                        //прибавить цену по магазину в массив компаний
                        $companyPayment[$shop->getCompany()->getId()]['price'] += $kktTariffPrice;
                        //прибавить kkts магазина в массив компаний
                        $companyPayment[$shop->getCompany()->getId()]['shops'][$shop->getId()] =
                            array_merge($companyPayment[$shop->getCompany()->getId()]['shops'][$shop->getId()], $kktTariff);
                    }
                }
            }
        }
        foreach ($companyPayment as $key => $company) {
            /** @var Company $currCompany */
            $currCompany = $this->entityManager->getRepository(Company::class)->find($key);

            /** @var User $testUser */
            $testUser = $this->entityManager->getRepository(User::class)->find(User::TEST_USER_ID);

            if ($currCompany->getBalance() < $company['price'] && $currCompany->getCompanyEmail() != $testUser->getEmail()) {
                $shopsUserPaymentTerm = [];
                foreach ($company['shops'] as $shopKey => $companyShop) {
                    $shopsPaymentTerm[$shopKey] = [];
                    /** @var Kkt $cKkt */
                    foreach ($companyShop as $cKkt) {
                        if ($cKkt->getFsVersion() === Kkt::FN_DEBUG_VERSION) {
                            $this->logger->addDebug('Тестовая касса #'.$cKkt->getSerialNumber().', пропускаем');
                            continue;
                        }
                        $this->logger->addWarning('Заканчивается срок оплаты кассы #'.$cKkt->getId());
                        $shopsPaymentTerm[$shopKey][] = $cKkt;
                        $shopsUserPaymentTerm[$shopKey][] = $cKkt;
                    }
                }
                if (\count($shopsUserPaymentTerm)) {
                    $users = $currCompany->getUser();
                    $mails = [];
                    /** @var User $u */
                    foreach ($users as $u) {
                        $mails[] = $u->getEmail();
                    }
                    $this->sendMail->sendExpiredPayment($shopsUserPaymentTerm, $mails);
                }
            }
        }
        if (\count($shopsPaymentTerm)) {
            $mails = explode(',', $this->entityManager->getRepository(Setting::class)->findOneBy(['param' => SettingService::EMAIL_FOR_COMPANY])->getValue());
            $this->sendMail->sendExpiredPayment($shopsPaymentTerm, $mails);
        }
    }
}
