<?php

namespace ApiInsalesTest\Service;

use ApiInsales\Service\InsalesSettingsService;
use ApiInsales\Service\WebhookCurlService;
use ApiInsales\Service\WebhookCurlServiceFactory;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class WebhookCurlServiceFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get(InsalesSettingsService::class)
            ->willReturn($this->prophesize(InsalesSettingsService::class));
    }

    public function testLoginHandlerFactory()
    {
        $factory = new WebhookCurlServiceFactory();
        $newClass = $factory($this->container->reveal(), WebhookCurlService::class);
        $this->assertInstanceOf(WebhookCurlService::class, $newClass);
    }
}
