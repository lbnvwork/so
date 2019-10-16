<?php


namespace Office\Command;

use Interop\Container\ContainerInterface;
use Monolog\Logger;
use Office\Service\SendMail;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class ServiceCommandFactory
 *
 * @package Office\Command
 */
class ServiceCommandFactory implements FactoryInterface
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
            $container->get(SendMail::class)
        );
    }
}
