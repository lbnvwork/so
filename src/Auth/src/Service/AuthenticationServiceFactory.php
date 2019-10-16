<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 19.01.18
 * Time: 14:27
 */

namespace Auth\Service;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class AuthenticationServiceFactory
 *
 * @package Auth\Service
 */
class AuthenticationServiceFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return AuthenticationService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container)
    {
        $em = $container->get('doctrine.entity_manager.orm_default');

        return new AuthenticationService($em);
    }
}
