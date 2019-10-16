<?php

declare(strict_types=1);

namespace CmsTest\Middleware;

use Cms\Middleware\ProcessingMiddleware;
use Cms\Middleware\ProcessingMiddlewareFactory;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class ProcessingMiddlewareFactoryTest extends TestCase
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
        $factory = new ProcessingMiddlewareFactory();

        $newClass = $factory($this->container->reveal(), ProcessingMiddleware::class);

        $this->assertInstanceOf(ProcessingMiddleware::class, $newClass);
    }
}