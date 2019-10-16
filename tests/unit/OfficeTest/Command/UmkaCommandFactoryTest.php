<?php

namespace OfficeTest\Command;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Office\Command\CheckFiscalizeCommand;
use Office\Command\FiscalizeCommand;
use Office\Command\UmkaCommandFactory;
use Office\Service\Umka;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class UmkaCommandFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get(Umka::class)->willReturn($this->prophesize(Umka::class));
        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
    }

    public function testCheckFiscalize()
    {
        $factory = new UmkaCommandFactory();

        $newClass = $factory($this->container->reveal(), CheckFiscalizeCommand::class);

        $this->assertInstanceOf(CheckFiscalizeCommand::class, $newClass);
    }

    public function testFiscalize()
    {
        $factory = new UmkaCommandFactory();

        $newClass = $factory($this->container->reveal(), FiscalizeCommand::class);

        $this->assertInstanceOf(FiscalizeCommand::class, $newClass);
    }
}
