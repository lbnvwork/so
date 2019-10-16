<?php

namespace ApiInsalesTest\Handler;

use ApiInsales\Handler\InstallHandler;
use ApiInsales\Handler\InstallHandlerFactory;
use ApiInsales\Handler\SettingsEditHandler;
use ApiInsales\Handler\SettingsEditHandlerFactory;
use App\Helper\UrlHelper;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class SettingsEditHandlerFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
        $this->container->get(UrlHelper::class)->willReturn($this->prophesize(UrlHelper::class));
    }

    public function testSettingsEditHandlerFactory()
    {
        $factory = new SettingsEditHandlerFactory();

        $newClass = $factory($this->container->reveal(), SettingsEditHandler::class);

        $this->assertInstanceOf(SettingsEditHandler::class, $newClass);
    }
}
