<?php

namespace ApiInsalesTest\Handler;

use ApiInsales\Handler\UninstallHandler;
use ApiInsales\Handler\UninstallHandlerFactory;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class UninstallHandlerFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get('doctrine.entity_manager.orm_default')
            ->willReturn($this->prophesize(EntityManager::class));
    }

    public function testUninstallHandlerFactory()
    {
        $factory = new UninstallHandlerFactory();
        $newClass = $factory($this->container->reveal(), UninstallHandler::class);
        $this->assertInstanceOf(UninstallHandler::class, $newClass);
    }
}
