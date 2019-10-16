<?php

namespace ApiInsalesTest\Service;

use ApiInsales\Handler\LoginHandler;
use ApiInsales\Service\LoginService;
use ApiInsales\Service\LoginServiceFactory;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class LoginServiceFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get('doctrine.entity_manager.orm_default')
            ->willReturn($this->prophesize(EntityManager::class));
    }

    public function testLoginServiceFactory()
    {
        $factory = new LoginServiceFactory();
        $newClass = $factory($this->container->reveal(), LoginService::class);
        $this->assertInstanceOf(LoginService::class, $newClass);
    }
}
