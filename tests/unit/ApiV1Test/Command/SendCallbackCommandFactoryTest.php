<?php

namespace ApiV1Test\Command;

use ApiV1\Command\SendCallbackCommand;
use ApiV1\Command\SendCallbackCommandFactory;
use ApiV1\Service\Check\Normal;
use ApiV1\Service\Umka\UmkaApi;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

class SendCallbackCommandFactoryTest extends TestCase
{
    public function testSendCallbackCommand(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturn(
            $this->createMock(EntityManager::class),
            $this->createMock(UmkaApi::class),
            $this->createMock(Normal::class)
        );

        $factory = new SendCallbackCommandFactory();
        $class = $factory($container, SendCallbackCommand::class);
        $this->assertInstanceOf(SendCallbackCommand::class, $class);
    }
}
