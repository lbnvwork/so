<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 22.01.18
 * Time: 10:30
 */
declare(strict_types=1);

namespace Office\Middleware;

use Interop\Container\ContainerInterface;

/**
 * Class CheckUserKktMiddlewareFactory
 *
 * @package Office\Middleware
 */
class CheckUserKktMiddlewareFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return CheckUserKktMiddleware
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container)
    {
        return new CheckUserKktMiddleware($container->get('doctrine.entity_manager.orm_default'));
    }
}
