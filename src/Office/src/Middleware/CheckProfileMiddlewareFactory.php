<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 22.01.18
 * Time: 10:30
 */

namespace Office\Middleware;

use App\Helper\UrlHelper;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Authentication\AuthenticationInterface;
use Zend\Expressive\Authentication\Exception\InvalidConfigException;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class CheckProfileMiddlewareFactory
 *
 * @package Office\Middleware
 */
class CheckProfileMiddlewareFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return CheckProfileMiddleware
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container)
    {
        return new CheckProfileMiddleware($container->get('doctrine.entity_manager.orm_default'), $container->get(UrlHelper::class));
    }
}
