<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 22.01.18
 * Time: 10:30
 */

namespace Auth\Middleware;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Authentication\AuthenticationInterface;
use Zend\Expressive\Authentication\Exception\InvalidConfigException;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class AuthenticationMiddlewareFactory
 *
 * @package Auth\Middleware
 */
class AuthenticationMiddlewareFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return AuthenticationMiddleware
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container)
    {
        $authentication = $container->has(AuthenticationInterface::class) ? $container->get(AuthenticationInterface::class) : null;
        if (null === $authentication) {
            throw new InvalidConfigException('AuthenticationInterface service is missing');
        }
        $template = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;

        return new AuthenticationMiddleware($authentication, $template);
    }
}
