<?php
declare(strict_types=1);

namespace ApiV1Test\Middleware;

use ApiV1\Middleware\ProcessingMiddleware;
use ApiV1\Middleware\ProcessingMiddlewareFactory;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class ProcessingMiddlewareFactoryTest extends TestCase
{
    public function testCanCreateProcessingMiddlewareFactory(): void
    {
        /** @var ContainerInterface|ObjectProphecy $container */
        $container = $this->prophesize(ContainerInterface::class);

        $container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
        $factory = new ProcessingMiddlewareFactory();

        $newClass = $factory($container->reveal(), ProcessingMiddleware::class);

        $this->assertInstanceOf(ProcessingMiddleware::class, $newClass);
    }
}
