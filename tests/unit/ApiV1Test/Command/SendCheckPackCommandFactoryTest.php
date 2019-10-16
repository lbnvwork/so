<?php

namespace ApiV1Test\Command;

use ApiV1\Command\SendCheckPackCommand;
use ApiV1\Command\SendCheckPackCommandFactory;
use ApiV1\Service\Check\Normal\Pack;
use ApiV1\Service\Umka\UmkaLkApi;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

class SendCheckPackCommandFactoryTest extends TestCase
{
    public function testSendCallbackCommand(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturn(
            $this->createMock(EntityManager::class),
            $this->createMock(Pack::class),
            $this->createMock(UmkaLkApi::class)
        );

        $factory = new SendCheckPackCommandFactory();
        $class = $factory($container, SendCheckPackCommand::class);
        $this->assertInstanceOf(SendCheckPackCommand::class, $class);
    }
}
