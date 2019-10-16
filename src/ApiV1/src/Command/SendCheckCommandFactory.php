<?php

declare(strict_types=1);

namespace ApiV1\Command;

use ApiV1\Service\Check\Correction;
use ApiV1\Service\Check\Normal;
use ApiV1\Service\Umka\UmkaApi;
use Interop\Container\ContainerInterface;
use Monolog\Logger;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class SendCheckCommandFactory
 *
 * @package ApiV1\Command
 */
class SendCheckCommandFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SendCheckCommand(
            new Logger('test'),
            $container->get('doctrine.entity_manager.orm_default'),
            $container->get(UmkaApi::class),
            $container->get(Normal::class),
            $container->get(Correction::class)
        );
    }
}
