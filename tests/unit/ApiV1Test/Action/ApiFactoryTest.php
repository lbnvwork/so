<?php

declare(strict_types=1);

namespace ApiV1Test\Action;

use ApiV1\Action\ApiFactory;
use ApiV1\Middleware\CheckRequestMiddleware;
use ApiV1\Service\Umka\UmkaApi;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class ApiFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
        $this->container->get(UmkaApi::class)->willReturn($this->prophesize(UmkaApi::class));
    }

    public function testFactoryCheckRequest()
    {
        $factory = new ApiFactory();

        $newClass = $factory($this->container->reveal(), CheckRequestMiddleware::class);

        $this->assertInstanceOf(CheckRequestMiddleware::class, $newClass);
    }

    public function testFactoryCheckRequestAtol()
    {
        $factory = new ApiFactory();

        $newClass = $factory($this->container->reveal(), \ApiAtolV1\Middleware\CheckRequestMiddleware::class);

        $this->assertInstanceOf(\ApiAtolV1\Middleware\CheckRequestMiddleware::class, $newClass);
    }
}