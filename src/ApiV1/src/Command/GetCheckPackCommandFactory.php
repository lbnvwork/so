<?php

declare(strict_types=1);

namespace ApiV1\Command;

use ApiV1\Service\Check\Normal;
use ApiV1\Service\Umka\UmkaLkApi;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Monolog\Logger;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class GetCheckPackCommandFactory
 *
 * @package ApiV1\Command
 */
class GetCheckPackCommandFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return GetCheckPackCommand|object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new GetCheckPackCommand(
            new Logger('test'),
            $container->get(EntityManager::class),
            $container->get(Normal\Pack::class),
            $container->get(UmkaLkApi::class)
        );
    }
}
