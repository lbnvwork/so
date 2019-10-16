<?php
declare(strict_types=1);

namespace Office\Action\Api;

use Interop\Container\ContainerInterface;
use Office\Service\KktService;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class GetKktInfoActionFactory
 *
 * @package Office\Action\Api
 */
class GetKktInfoActionFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return GetkktInfoAction
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GetkktInfoAction
    {
        return new GetkktInfoAction($container->get('doctrine.entity_manager.orm_default'), $container->get(KktService::class));
    }
}
