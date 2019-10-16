<?php
declare(strict_types=1);

namespace ApiV1\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class TokenServiceFactory
 *
 * @package ApiV1\Service
 */
class TokenServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return TokenService|object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new TokenService($container->get('doctrine.entity_manager.orm_default'));
    }
}
