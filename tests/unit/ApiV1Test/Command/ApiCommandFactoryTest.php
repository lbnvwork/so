<?php

declare(strict_types=1);

namespace ApiV1Test\Command;

use ApiV1\Command\ApiCommandFactory;
use ApiV1\Command\CheckStatusCommand;
use ApiV1\Command\CloseFnCommand;
use ApiV1\Command\CloseShiftCommand;
use ApiV1\Command\SendCallbackCommand;
use ApiV1\Command\SendCheckCommand;
use ApiV1\Service\Umka\UmkaApi;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class ApiCommandFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
        $this->container->get(UmkaApi::class)->willReturn($this->prophesize(UmkaApi::class));
    }

    public function testFactoryCheckStatus()
    {
        $factory = new ApiCommandFactory();

        $newClass = $factory($this->container->reveal(), CheckStatusCommand::class);

        $this->assertInstanceOf(CheckStatusCommand::class, $newClass);
    }

    public function testFactoryCloseFn()
    {
        $factory = new ApiCommandFactory();

        $newClass = $factory($this->container->reveal(), CloseFnCommand::class);

        $this->assertInstanceOf(CloseFnCommand::class, $newClass);
    }

    public function testFactoryCloseShift()
    {
        $factory = new ApiCommandFactory();

        $newClass = $factory($this->container->reveal(), CloseShiftCommand::class);

        $this->assertInstanceOf(CloseShiftCommand::class, $newClass);
    }
}