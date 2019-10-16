<?php

declare(strict_types=1);

namespace AppTest\Command;

use App\Command\InitAppCommand;
use App\Command\InitAppCommandFactory;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class InitAppComandFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
    }

    public function testFactory()
    {
        $factory = new InitAppCommandFactory();

        $newClass = $factory($this->container->reveal(), InitAppCommand::class);

        $this->assertInstanceOf(InitAppCommand::class, $newClass);
    }
}