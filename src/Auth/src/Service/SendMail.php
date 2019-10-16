<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 16.03.18
 * Time: 23:10
 */

namespace Auth\Service;

use Auth\Entity\User;

/**
 * Class SendMail
 * @package App\Service
 */
class SendMail
{
    private $config = [];

    /**
     * SendMail constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param User $user
     * @return int
     */
    public function sendNewRegister(User $user)
    {
        // Create a message
        $message = (new \Swift_Message())
            ->setSubject('Регистрация на сайте '.$this->config['siteUrl'])
            ->setFrom($this->config['from'])
            ->setTo([$user->getEmail() => $user->getFIO()])
            ->setBody('Для продолжения регистрации перейдите по ссылке '.$this->config['siteUrl'].'/user/confirm/'.$user->getHashKey().PHP_EOL);

        return $this->send($message);
    }

    /**
     * @param User $user
     * @return int
     */
    public function restorePassword(User $user)
    {
        // Create a message
        $message = (new \Swift_Message())
            ->setSubject('Восстановление пароля на сайте   '.$this->config['siteUrl'])
            ->setFrom($this->config['from'])
            ->setTo([$user->getEmail() => $user->getFIO()])
            ->setBody('Для подтверждения востановления пароля перейдите по ссылке '.$this->config['siteUrl'].'/user/restore/'.$user->getHashKey());

        return $this->send($message);
    }

    /**
     * @param User $user
     * @param string $password
     * @return int
     */
    public function sendNewPassword(User $user, string $password)
    {
        // Create a message
        $message = (new \Swift_Message())
            ->setSubject('Восстановление пароля')
            ->setFrom($this->config['from'])
            ->setTo([$user->getEmail() => $user->getFIO()])
            ->setBody('Новый пароль для входа: '.$password);

        return $this->send($message);
    }

    /**
     * @param \Swift_Message $message
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
