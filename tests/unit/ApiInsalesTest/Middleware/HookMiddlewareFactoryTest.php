<?php

namespace ApiInsalesTest\Middleware;

use ApiInsales\Middleware\HookMiddleware;
use ApiInsales\Middleware\HookMiddlewareFactory;
use ApiInsales\Service\WebhookCurlService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Zend\Expressive\Template\TemplateRendererInterface;

class HookMiddlewareFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
        $this->container
            ->get(TemplateRendererInterface::class)
            ->willReturn($this->prophesize(TemplateRendererInterface::class));
        $this->container->get(WebhookCurlService::class)->willReturn($this->prophesize(WebhookCurlService::class));
    }

    public function testHookMiddlewareFactory()
    {
        $factory = new HookMiddlewareFactory();

        $newClass = $factory($this->container->reveal(), HookMiddleware::class);

        $this->assertInstanceOf(HookMiddleware::class, $newClass);
    }
}
