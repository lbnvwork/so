<?php

declare(strict_types=1);

namespace ApiV1\Command;

use ApiV1\Service\Check\Normal;
use ApiV1\Service\Umka\UmkaApi;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Monolog\Logger;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class SendCallbackCommandFactory
 *
 * @package ApiV1\Command
 */
class SendCallbackCommandFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return SendCallbackCommand
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SendCallbackCommand(
            new Logger('test'),
            $container->get(EntityManager::class),
            $container->get(UmkaApi::class),
            $container->get(Normal::class)
        );
    }
}
