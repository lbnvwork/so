<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\AppPageFactory;
use App\Handler\RetargetPageHandler;
use App\Helper\UrlHelper;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class AppPageFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get(UrlHelper::class)->willReturn($this->prophesize(UrlHelper::class));
        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
    }

    public function testFactoryWithTemplate()
    {
        $this->container->has(TemplateRendererInterface::class)->willReturn(true);
        $this->container
            ->get(TemplateRendererInterface::class)
            ->willReturn($this->prophesize(TemplateRendererInterface::class));

        $factory = new AppPageFactory();

        $newClass = $factory($this->container->reveal(), RetargetPageHandler::class);

        $this->assertInstanceOf(RetargetPageHandler::class, $newClass);
    }
}