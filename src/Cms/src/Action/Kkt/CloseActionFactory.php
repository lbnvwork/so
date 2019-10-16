<?php
declare(strict_types=1);

namespace Cms\Action\Kkt;

use ApiV1\Service\Umka\UmkaApi;
use App\Helper\UrlHelper;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class CloseActionFactory
 *
 * @package Cms\Action\Kkt
 */
class CloseActionFactory implements FactoryInterface
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
        return new $requestedName(
            $container->get('doctrine.entity_manager.orm_default'),
            $container->get(UrlHelper::class),
            $container->get(UmkaApi::class)
        );
    }
}
