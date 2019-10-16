<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 19.01.18
 * Time: 10:56
 */

namespace Auth\Handler;

use App\Helper\UrlHelper;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Authentication\UserRepositoryInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class LoginActionFactory
 *
 * @package Auth\Action
 */
class LoginHandlerFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return LoginHandler
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container)
    {
        return new LoginHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(UserRepositoryInterface::class),
            $container->get(UrlHelper::class),
            $container->get('doctrine.entity_manager.orm_default')
        );
    }
}
