<?php

namespace ApiInsalesTest\Handler;

use ApiInsales\Handler\SettingsEditHandler;
use ApiInsales\Handler\SettingsViewHandler;
use ApiInsales\Handler\SettingsViewHandlerFactory;
use App\Handler\HomePageHandler;
use App\Handler\HomePageHandlerFactory;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Zend\Expressive\Template\TemplateRendererInterface;

class SettingsViewHandlerFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container
            ->get(TemplateRendererInterface::class)
            ->willReturn($this->prophesize(TemplateRendererInterface::class));
        $this->container
            ->get('doctrine.entity_manager.orm_default')
            ->willReturn($this->prophesize(EntityManager::class));
    }

    public function testSettingsViewHandlerFactory()
    {
        $factory = new SettingsViewHandlerFactory();
        $newClass = $factory($this->container->reveal(), SettingsViewHandler::class);
        $this->assertInstanceOf(SettingsViewHandler::class, $newClass);
    }
}
