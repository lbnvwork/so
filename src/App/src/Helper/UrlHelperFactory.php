<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 08.02.18
 * Time: 13:34
 */

namespace App\Helper;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Helper\Exception\MissingRouterException;
use Zend\Expressive\Router\RouterInterface;

/**
 * Class UrlHelperFactory
 *
 * @package App\Helper
 */
class UrlHelperFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return UrlHelper
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(RouterInterface::class)) {
            throw new MissingRouterException(sprintf(
                '%s requires a %s implementation; none found in container',
                UrlHelper::class,
                RouterInterface::class
            ));
        }

        return new UrlHelper($container->get(RouterInterface::class));
    }
}
