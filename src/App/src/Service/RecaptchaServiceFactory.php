<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 19.01.18
 * Time: 14:27
 */

namespace App\Service;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Authorization\Exception\InvalidConfigException;

/**
 * Class AuthenticationServiceFactory
 *
 * @package Auth\Service
 */
class RecaptchaServiceFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return RecaptchaService
     */

    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        return new RecaptchaService();
    }
}
