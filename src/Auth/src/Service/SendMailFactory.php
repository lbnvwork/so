<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 19.01.18
 * Time: 14:27
 */

namespace Auth\Service;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Authorization\Exception\InvalidConfigException;

/**
 * Class AuthenticationServiceFactory
 *
 * @package Auth\Service
 */
class SendMailFactory
{
    /**
     * @param ContainerInterface $container
     * @return SendMail
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        if (!isset($config['sendMail'])) {
            throw new InvalidConfigException('Email not configured');
        }

        return new SendMail($config['sendMail']);
    }
}
