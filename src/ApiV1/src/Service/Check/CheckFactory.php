<?php
declare(strict_types=1);

namespace ApiV1\Service\Check;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class CheckFactory
 *
 * @package ApiV1\Service\Check
 */
class CheckFactory implements FactoryInterface
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
            $container->get('doctrine.entity_manager.orm_default')
        );
    }
}
