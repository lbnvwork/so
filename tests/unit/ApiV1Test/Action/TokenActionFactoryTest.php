<?php

declare(strict_types=1);

namespace ApiV1Test\Action;

use ApiV1\Action\TokenAction;
use ApiV1\Action\TokenActionFactory;
use ApiV1\Service\TokenService;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class TokenActionFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
        $this->container->get(TokenService::class)->willReturn($this->prophesize(TokenService::class));
    }

    public function testFactoryToken()
    {
        $factory = new TokenActionFactory();

        $newClass = $factory($this->container->reveal(), TokenAction::class);

        $this->assertInstanceOf(TokenAction::class, $newClass);
    }

    public function testFactoryTokenAtol()
    {
        $factory = new TokenActionFactory();

        $newClass = $factory($this->container->reveal(), \ApiAtolV1\Action\TokenAction::class);

        $this->assertInstanceOf(\ApiAtolV1\Action\TokenAction::class, $newClass);
    }
}