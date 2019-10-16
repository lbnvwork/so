<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 16.03.18
 * Time: 23:10
 */

namespace App\Service;

use Doctrine\ORM\EntityManager;

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
     */
    public function __construct(array $config, EntityManager $entityManager)
    {
        $this->config = $config;
        $this->entityManager = $entityManager;
    }

    /**
     * Первый шаг отправки промокода
     *
     * @param string $email
     *
     * @return int
     */
    public function sendPromoStepOne(string $email): int
    {
        $body = [];

        $body[] = 'Здравствуйте!';
        $body[] = '';
        $body[] = 'Для получения промокода* платежной системы «Robokassa» от компании «Счетмаш»:';
        $body[] = '';
        $body[] = '1. Подключите интернет-кассу «Счетмаш Онлайн».';
        $body[] = 'Этот сервис будет взаимодействовать с сайтом и передавать чеки покупателям.';
        $body[] = 'Мы предоставим Вам сервис «Счетмаш Онлайн» https://online.schetmash.com/ в первый месяц совершенно бесплатно, а также подарим Вам услуги ОФД!';
        $body[] = '';
        $body[] = '2. После чего, Вам придет промокод и ссылка на регистрацию на сайте платежной системы «Robokassa»';
        $body[] = 'Через нее покупатель будет оплачивать покупки на сайте любым способом.';
        $body[] = '';
        $body[] = '3. Получите скидку и пользуйтесь выгодным тарифом!';
        $body[] = '';
        $body[] = 'Что дает Вам промокод?';
        $body[] = 'Вы получаете скидку от платежной системы «Robokassa».';
        $body[] = 'Вы можете пользоваться наиболее экономным тарифом «Базовый» 3 месяца!';
        $body[] = '';
        $body[] = '*Промокод активен только при использовании интернет-кассы «Счетмаш Онлайн».';


        // Create a message
        $message = (new \Swift_Message('Ваш промокод от компании «Счетмаш Онлайн»'))
            ->setFrom($this->config['from'])
            ->setTo($email)
            ->setBody(implode(PHP_EOL, $body));

        return $this->send($message);
    }

    /**
     * @param array $form
     *
     * @return int
     */
    public function sendNeedCms(array $form): int
    {
        $fields = [
            'fio'   => 'ФИО',
            'email' => 'Электронный адрес',
            'text'  => 'Текст сообщения',
            'cms'   => 'CMS'
        ];
        $body = [];
        foreach ($fields as $key => $field) {
            $body[] = $field.': '.$form[$key];
        }

        // Create a message
        $message = (new \Swift_Message('Запрос новой интеграции'))
            ->setFrom($this->config['from'])
            ->setTo('support@keaz.ru')
//            ->setTo('spozdnyakov@keaz.ru')
            ->setBody(implode(PHP_EOL, $body));

        return $this->send($message);
    }

    /**
     * @param \Swift_Message $message
     *
     * @return int
     */
    private function send(\Swift_Message $message): int
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
