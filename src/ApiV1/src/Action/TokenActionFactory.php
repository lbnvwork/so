<?php
declare(strict_types=1);

namespace ApiV1\Action;

use ApiV1\Service\TokenService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class TokenActionFactory
 *
 * @package ApiV1\Action
 */
class TokenActionFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new $requestedName($container->get('doctrine.entity_manager.orm_default'), $container->get(TokenService::class));
    }
}
