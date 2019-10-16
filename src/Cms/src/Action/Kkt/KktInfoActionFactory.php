<?php
declare(strict_types=1);

namespace Cms\Action\Kkt;

use App\Helper\UrlHelper;
use Interop\Container\ContainerInterface;
use Office\Service\KktService;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class KktInfoActionFactory
 *
 * @package Cms\Action\Kkt
 */
class KktInfoActionFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return KktInfoAction|object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new KktInfoAction(
            $container->get('doctrine.entity_manager.orm_default'),
            $container->get(KktService::class),
            $container->get(UrlHelper::class)
        );
    }
}
