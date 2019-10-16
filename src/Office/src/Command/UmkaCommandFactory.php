<?php


namespace Office\Command;

use Interop\Container\ContainerInterface;
use Monolog\Logger;
use Office\Service\Umka;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class UmkaCommandFactory
 *
 * @package Office\Command
 */
class UmkaCommandFactory implements FactoryInterface
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
            new Logger('test'),
            $container->get('doctrine.entity_manager.orm_default'),
            $container->get(Umka::class)
        );
    }
}
