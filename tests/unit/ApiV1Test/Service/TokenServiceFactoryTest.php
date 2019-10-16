<?php

declare(strict_types=1);

namespace ApiV1Test\Service;

use ApiV1\Service\TokenService;
use ApiV1\Service\TokenServiceFactory;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class TokenServiceFactoryTest extends TestCase
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
        $factory = new TokenServiceFactory();

        $newClass = $factory($this->container->reveal(), TokenService::class);

        $this->assertInstanceOf(TokenService::class, $newClass);
    }
}