<?php

declare(strict_types=1);

namespace ApiV1\Command;

use ApiV1\Service\Check\Normal;
use ApiV1\Service\Umka\UmkaLkApi;
use Interop\Container\ContainerInterface;
use Monolog\Logger;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class SendCheckPackCommandFactory
 *
 * @package ApiV1\Command
 */
class SendCheckPackCommandFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return SendCheckPackCommand|object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SendCheckPackCommand(
            new Logger('test'),
            $container->get('doctrine.entity_manager.orm_default'),
            $container->get(Normal\Pack::class),
            $container->get(UmkaLkApi::class)
        );
    }
}
