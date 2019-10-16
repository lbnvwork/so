<?php

namespace ApiV1Test\Command;

use ApiV1\Command\GetCheckPackCommand;
use ApiV1\Command\GetCheckPackCommandFactory;
use ApiV1\Service\Check\Normal\Pack;
use ApiV1\Service\Umka\UmkaLkApi;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

class GetCheckPackCommandFactoryTest extends TestCase
{
    public function testCreateGetCheckPackCommand(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturn(
            $this->createMock(EntityManager::class),
            $this->createMock(Pack::class),
            $this->createMock(UmkaLkApi::class)
        );

        $factory = new GetCheckPackCommandFactory();
        $class = $factory($container, GetCheckPackCommand::class);
        $this->assertInstanceOf(GetCheckPackCommand::class, $class);
    }
}
