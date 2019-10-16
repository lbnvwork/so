<?php

declare(strict_types=1);

namespace AuthTest\Service;

use Auth\Service\SendMail;
use Auth\UserRepository\Database;
use Auth\UserRepository\DatabaseFactory;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class DatabaseFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
        $this->container->get(SendMail::class)->willReturn($this->prophesize(SendMail::class));
    }

    public function testFactory()
    {
        $factory = new DatabaseFactory();

        $newClass = $factory($this->container->reveal());

        $this->assertInstanceOf(Database::class, $newClass);
    }
}