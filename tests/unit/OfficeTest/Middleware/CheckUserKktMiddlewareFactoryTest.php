<?php

declare(strict_types=1);

namespace OfficeTest\Middleware;

use Doctrine\ORM\EntityManager;
use Office\Middleware\CheckUserKktMiddleware;
use Office\Middleware\CheckUserKktMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class CheckUserKktMiddlewareFactoryTest extends TestCase
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
        $factory = new CheckUserKktMiddlewareFactory();

        $newClass = $factory($this->container->reveal());

        $this->assertInstanceOf(CheckUserKktMiddleware::class, $newClass);
    }
}