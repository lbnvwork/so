<?php

declare(strict_types=1);

namespace OfficeTest\Middleware;

use Doctrine\ORM\EntityManager;
use Office\Middleware\CheckCompanyMiddleware;
use Office\Middleware\CheckCompanyMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class CheckComapnyMiddlewareFactoryTest extends TestCase
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
        $factory = new CheckCompanyMiddlewareFactory();

        $newClass = $factory($this->container->reveal());

        $this->assertInstanceOf(CheckCompanyMiddleware::class, $newClass);
    }
}