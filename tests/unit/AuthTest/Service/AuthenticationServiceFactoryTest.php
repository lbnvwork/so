<?php

declare(strict_types=1);

namespace AuthTest\Service;

use Auth\Service\AuthenticationServiceFactory;
use Auth\Service\AuthenticationService;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class AuthenticationServiceFactoryTest extends TestCase
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
        $factory = new AuthenticationServiceFactory();

        $newClass = $factory($this->container->reveal());

        $this->assertInstanceOf(AuthenticationService::class, $newClass);
    }
}