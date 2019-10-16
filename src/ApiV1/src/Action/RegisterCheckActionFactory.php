<?php
declare(strict_types=1);

namespace ApiV1\Action;

use ApiV1\Service\Check\Correction;
use ApiV1\Service\Check\Normal;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class RegisterCheckActionFactory
 *
 * @package ApiV1\Action
 */
class RegisterCheckActionFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return mixed|object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new $requestedName(
            $container->get('doctrine.entity_manager.orm_default'),
            $container->get(Normal::class),
            $container->get(Correction::class)
        );
    }
}
