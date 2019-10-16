<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 12.08.19
 * Time: 14:13
 */

namespace ApiInsales\Service;

use Interop\Container\ContainerInterface;

/**
 * Class LoginServiceFactory
 *
 * @package ApiInsales\Service
 */
class LoginServiceFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return LoginService
     */
    public function __invoke(ContainerInterface $container)
    {
        $em = $container->get('doctrine.entity_manager.orm_default');
        return new LoginService($em);
    }
}
