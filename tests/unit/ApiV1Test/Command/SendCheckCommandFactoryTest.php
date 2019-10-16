<?php

declare(strict_types=1);

namespace ApiV1Test\Command;

use ApiV1\Command\SendCheckCommand;
use ApiV1\Command\SendCheckCommandFactory;
use ApiV1\Service\Check\Correction;
use ApiV1\Service\Check\Normal;
use ApiV1\Service\Umka\UmkaApi;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class SendCheckCommandFactoryTest extends TestCase
{
    public function testFactory()
    {
        /** @var ContainerInterface|ObjectProphecy $container */
        $container = $this->prophesize(ContainerInterface::class);

        $container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
        $container->get(UmkaApi::class)->willReturn($this->prophesize(UmkaApi::class));
        $container->get(Normal::class)->willReturn($this->prophesize(Normal::class));
        $container->get(Correction::class)->willReturn($this->prophesize(Correction::class));

        $factory = new SendCheckCommandFactory();

        $newClass = $factory($container->reveal(), SendCheckCommand::class);

        $this->assertInstanceOf(SendCheckCommand::class, $newClass);
    }
}