<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 16.03.18
 * Time: 23:10
 */

namespace Office\Service;

use App\Entity\Setting;
use App\Helper\UrlHelper;
use Auth\Entity\User;
use Cms\Service\SettingService;
use Doctrine\ORM\EntityManager;
use Office\Entity\Company;
use Office\Entity\Kkt;
use Office\Entity\Shop;
use Office\Entity\Tariff;

/**
 * Class SendMail
 *
 * @package App\Service
 */
class SendMail
{
    private $config = [];

    private $entityManager;

    /**
     * SendMail constructor.
     *
     * @param array $config
     * @param EntityManager $entityManager
     * @param UrlHelper $urlHelper
     */
    public function __construct(array $config, EntityManager $entityManager, UrlHelper $urlHelper)
    {
        $this->config = $config;
        $this->entityManager = $entityManager;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param Company $company
     *
     * @return int
     */
    public function sendRegisterNewCompany(Company $company)
    {
        /** @var User $user */
        $user = $company->getUser()->current(); //TODO ошибочно
        $body = [];
        $body[] = 'Уважаемый администратор.';
        $body[] = 'В сервисе "Счетмаш-онлайн" пользователем ID: '.$user->getId().' ('.$user->getFIO().') зарегистрирована новая компания '.$company->getTitle().':';
        $body[] = '';
        $body[] = 'ИНН - '.$company->getInn();
        $body[] = 'КПП - '.$company->getKpp();
        $body[] = 'ОРГН - '.$company->getOgrn();
        $body[] = 'Адрес - '.$company->getAddress();
        $body[] = 'Представитель - '.$company->getDirectorLastName().' '.$company->getDirectorFirstName().' '.$company->getDirectorMiddleName();


        $companyEmails = $this->entityManager->getRepository(Setting::class)->findOneBy(['param' => SettingService::EMAIL_FOR_COMPANY])->getValue();
        $mails = explode(',', $companyEmails);

        if (\count($mails)) {
            // Create a message
            $message = (new \Swift_Message('Регистрация компании'))
                ->setFrom($this->config['from'])
                ->setTo($mails)
                ->setBody(implode(PHP_EOL, $body));

            return $this->send($message);
        }

        return 0;
    }

    /**
     * @param Shop $shop
     *
     * @return int
     */
    public function sendRegisterNewShop(Shop $shop)
    {
        /** @var User $user */
        $user = $shop->getCompany()->getUser()->current(); //TODO ошибочно
        $body = [];
        $body[] = 'Уважаемый администратор.';
        $body[] = 'В сервисе "Счетмаш-онлайн" пользователем ID: '.
            $user->getId().' ('.$user->getFIO().') компания '.
            $shop->getCompany()->getTitle().' зарегистрировала новый магазин.';
        $body[] = '';
        $body[] = 'Название - '.$shop->getTitle();
        $body[] = 'Url - '.$shop->getUrl();

        $companyEmails = $this->entityManager->getRepository(Setting::class)->findOneBy(['param' => SettingService::EMAIL_FOR_COMPANY])->getValue();
        $mails = explode(',', $companyEmails);

        if (\count($mails)) {
            // Create a message
            $message = (new \Swift_Message('Регистрация магазина'))
                ->setFrom($this->config['from'])
                ->setTo($mails)
                ->setBody(implode(PHP_EOL, $body));

            return $this->send($message);
        }

        return 0;
    }

    /**
     * @param Shop $shop
     * @param int $countKkt
     *
     * @return int
     */
    public function sendAddKkt(Shop $shop, int $countKkt)
    {
        /** @var User $user */
        $user = $shop->getCompany()->getUser()->current(); //TODO ошибочно
        $body = [];
        $body[] = 'Уважаемый администратор.';
        $body[] = 'В сервисе "Счетмаш-онлайн" пользователем ID: '.
            $user->getId().' ('.$user->getFIO().') компания '.
            $shop->getCompany()->getTitle().' для магазина '.$shop->getTitle().'.';
        $body[] = 'запросила к аренде '.$countKkt.' кассы';
        $body[] = 'Необходимо создать в ERP системе заказ покупателя';

        $companyEmails = $this->entityManager->getRepository(Setting::class)->findOneBy(['param' => SettingService::EMAIL_FOR_COMPANY])->getValue();
        $mails = explode(',', $companyEmails);

        if (\count($mails)) {
            // Create a message
            $message = (new \Swift_Message('Аренда ККТ'))
                ->setFrom($this->config['from'])
                ->setTo($mails)
                ->setBody(implode(PHP_EOL, $body));

            return $this->send($message);
        }

        return 0;
    }

    /**
     * @param Shop $shop
     * @param array $kkts
     *
     * @return int
     */
    public function sendNotUseKkt(array $kkts)
    {
        $body = [];
        $body[] = 'Уважаемый администратор.';
        foreach ($kkts as $shopId => $items) {
            $shop = $this->entityManager->getRepository(Shop::class)->find($shopId);
            $body[] = 'В сервисе "Счетмаш-онлайн" у магазина '.$shop->getTitle().', компания '.$shop->getCompany()->getTitle().' не используются кассы:';
            foreach ($items as $item) {
                /** @var Kkt $kkt */
                $kkt = $item['kkt'];
                $body[] = '#'.$kkt->getSerialNumber().', РНМ '.$kkt->getRegNumber().' - '.$item['days'].' дней';
            }
        }
        $body[] = '';
        $body[] = 'Нужно с этим что-то делать...';

        $companyEmails = $this->entityManager->getRepository(Setting::class)->findOneBy(['param' => SettingService::EMAIL_FOR_COMPANY])->getValue();
        $mails = explode(',', $companyEmails);

        if (\count($mails)) {
            // Create a message
            $message = (new \Swift_Message('Неиспользуемые ККТ'))
                ->setFrom($this->config['from'])
                ->setTo($mails)
                ->setBody(implode(PHP_EOL, $body));

            return $this->send($message);
        }

        return 0;
    }

    /**
     * @param array $kkts
     * @param array $mails
     *
     * @return int
     */
    public function sendExpiredPayment(array $kkts, array $mails)
    {
        $body = [];
        $body[] = 'Добрый день!';
        foreach ($kkts as $shopId => $items) {
            /** @var Shop $shop */
            $shop = $this->entityManager->getRepository(Shop::class)->find($shopId);
            $body[] = 'Обращаем внимание, что через 7 дней у магазина '.
                $shop->getTitle().', компании '.
                $shop->getCompany()->getTitle().' будет списана плата для касс:';
            /** @var Kkt $kkt */
            foreach ($items as $kkt) {
                $body[] = '#'.$kkt->getSerialNumber().', РНМ '.
                    $kkt->getRegNumber().' - '.
                    $kkt->getDateExpired()->format('d.m.Y').' по тарифу '.$kkt->getTariff()->getTitle();
            }
        }

        $body[] = '';
        $body[] = 'Рекомендуем проверить баланс и пополнить его при необходимости.';
        $body[] = '';
        $body[] = 'Для выставления счета обратитесь к личному менеджеру.';
        $body[] = 'тел.: +7 (4712) 73-49-90';
        $body[] = 'email: online@schetmash.com';

        if (\count($mails)) {
            // Create a message
            $message = (new \Swift_Message('Через 7 дней будет списана плата за использование ККТ'))
                ->setFrom($this->config['from'])
                ->setTo($mails)
                ->setBody(implode(PHP_EOL, $body));

            return $this->send($message);
        }

        return 0;
    }

    /**
     * @param array $kkts
     * @param array $mails
     *
     * @return int
     */
    public function sendTariffMaxTurnover(array $kkts, array $mails)
    {
        $body = [];
        if (\count($kkts)) {
            $body[] = 'Добрый день!';
            foreach ($kkts as $shopId => $items) {
                /** @var Shop $shop */
                $shop = $this->entityManager->getRepository(Shop::class)->find($shopId);
                /** @var Tariff $defaultTariff */
                $defaultTariff = $this->entityManager->getRepository(Tariff::class)->findOneBy(['isDefault'=>true]);

                $body[] = 'В сервисе "Счетмаш-онлайн" у магазина '.$shop->getTitle().', компания '.$shop->getCompany()->getTitle().'для касс: ';
                /** @var Kkt $kkt */
                foreach ($items as $kkt) {
                    $body[] = '#'.$kkt->getSerialNumber()
                        .', РНМ '.$kkt->getRegNumber()
                        .' - '.$kkt->getDateExpired()->format('d.m.Y H:i:s')
                        .' по тарифу '.$kkt->getTariff()->getTitle()
                        .' превышен ежемесячный оборот в '.$kkt->getTariff()->getMaxTurnover().' руб.';
                    $body[] = 'Тарифный план изменен на '. $defaultTariff->getTitle()
                            .', Вам будет выставлен счет, во избежание блокировки просьба оплатить';
                }
            }
        }
        if (\count($mails)) {
            // Create a message
            $message = (new \Swift_Message('Превышен оборот ККТ по тарифу "'.$items[0]->getTariff()->getTitle().'"'))
                ->setFrom($this->config['from'])
                ->setTo($mails)
                ->setBody(implode(PHP_EOL, $body));

            return $this->send($message);
        }

        return 0;
    }

    /**
     * @param array $kkts
     * @param array $mails
     *
     * @return int
     */
    public function sendNoPayment(array $kkts, array $mails)
    {
        $body = [];
        if (\count($kkts)) {
            $body[] = 'Добрый день!';
            foreach ($kkts as $shopId => $items) {
                /** @var Shop $shop */
                $shop = $this->entityManager->getRepository(Shop::class)->find($shopId);
                $body[] = 'У магазина '.$shop->getTitle().', компани '.$shop->getCompany()->getTitle().' выставлен счет за предыдущий месяц для касс: ';
                /** @var Kkt $kkt */
                foreach ($items as $kkt) {
                    $price = $this->entityManager->getRepository(Kkt::class)->getKktPrice($kkt);
                    $body[] =
                        '#'.$kkt->getSerialNumber().', РНМ '.
                        $kkt->getRegNumber().' - '.
                        $kkt->getDateExpired()->format('d.m.Y H:i:s').' по тарифу '.
                        $kkt->getTariff()->getTitle().'Неоплаченный остаток по счету: '.$price.' руб.';
                }
            }
            $body[] = '';
            $body[] = 'Рекомендуем Вас пополнить счет и возобновить работу ККТ.';
            $body[] = 'тел.: +7 (4712) 73-49-90';
            $body[] = 'email: online@schetmash.com';
        }
        if (\count($mails)) {
            // Create a message
            $message = (new \Swift_Message('Недостаточно средств для оплаты. Работа ККТ приостановлена'))
                ->setFrom($this->config['from'])
                ->setTo($mails)
                ->setBody(implode(PHP_EOL, $body));

            return $this->send($message);
        }

        return 0;
    }

    /**
     * @param string $email
     *
     * @return int
     */
    public function sendPromoStepTwo(string $email): int
    {
        $body = [];

        $body[] = 'Здравствуйте!';
        $body[] = '';
        $body[] = 'Ваш промокод от компании «Счетмаш»: 01SMRK';
        $body[] = '';
        $body[] = 'Что дает Вам промокод?';
        $body[] = '';
        $body[] = 'Вы получаете скидку от платежной системы «Robokassa». Вы можете пользоваться наиболее экономным тарифом «Базовый» 3 месяца!';
        $body[] = '';
        $body[] = 'Как его использовать?';
        $body[] = '';
        $body[] = 'Зарегистрируйтесь и введите промокод на сайте платежной системы «Robokassa»';
        $body[] = 'https://partner.robokassa.ru/Reg/Register?culture=ru&_ga=2.160845053.1904168837.1530002050-1755739109.1522748138';
        $body[] = 'Через нее покупатель будет оплачивать покупки на сайте любым способом.';
        $body[] = '';
        $body[] = 'Получите скидку и пользуйтесь выгодным тарифом!';
        $body[] = '';
        $body[] = '*Промокод активен только при использовании интернет-кассы «Счетмаш Онлайн».';

        $message = (new \Swift_Message('Ваш промокод от компании «Счетмаш Онлайн»'))
            ->setFrom($this->config['from'])
            ->setTo($email)
            ->setBody(implode(PHP_EOL, $body));

        return $this->send($message);
    }

    /**
     * @param Company $company
     * @param array $mails
     *
     * @return int
     */
    public function sendNoMoney(Company $company, array $mails): int
    {
        $body = [];
        $body[] = 'Добрый день!';
        $body[] = 'На счете компании '.$company->getTitle().' недостаточно средств для оплаты!';
        $body[] = 'Рекомендуем пополнить баланс.';
        $body[] = '';
        $body[] = 'https://online.schetmash.com/lk/billing/invoice';
        $body[] = 'тел.: +7 (4712) 73-49-90';
        $body[] = 'email: online@schetmash.com';

        if (\count($mails)) {
            // Create a message
            $message = (new \Swift_Message('Работа ККТ приостановлена'))
                ->setFrom($this->config['from'])
                ->setTo($mails)
                ->setBody(implode(PHP_EOL, $body));

            return $this->send($message);
        }

        return 0;
    }

    /**
     * @param \Swift_Message $message
     *
     * @return int
     */
    private function send(\Swift_Message $message)
    {
        // Create the Transport
        $transport = (new \Swift_SmtpTransport($this->config['host'], $this->config['port'], null))//$this->config['encryption']))
        ->setUsername($this->config['login'])
            ->setPassword($this->config['password']);

        // Create the Mailer using your created Transport
        $mailer = new \Swift_Mailer($transport);

        // Send the message
        return $mailer->send($message);
    }
}
