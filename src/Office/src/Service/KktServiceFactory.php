<?php
declare(strict_types=1);

namespace Office\Service;

use ApiV1\Service\Umka\UmkaApi;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class KktServiceFactory
 *
 * @package Office\Service
 */
class KktServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return KktService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): KktService
    {
        return new KktService(
            $container->get('doctrine.entity_manager.orm_default'),
            $container->get(Umka::class),
            $container->get(UmkaApi::class)
        );
    }
}
