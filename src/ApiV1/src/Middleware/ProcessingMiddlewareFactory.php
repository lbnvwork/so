<?php
declare(strict_types=1);

namespace ApiV1\Middleware;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class ProcessingMiddlewareFactory
 *
 * @package ApiV1\Middleware
 */
class ProcessingMiddlewareFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return ProcessingMiddleware|object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ProcessingMiddleware($container->get('doctrine.entity_manager.orm_default'));
    }
}
