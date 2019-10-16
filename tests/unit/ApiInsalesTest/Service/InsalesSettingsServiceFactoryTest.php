<?php

namespace ApiInsalesTest\Service;

use ApiInsales\Service\InsalesSettingsService;
use ApiInsales\Service\InsalesSettingsServiceFactory;
use App\Helper\UrlHelper;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class InsalesSettingsServiceFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $urlHelper = $this->prophesize(UrlHelper::class);
        $urlHelper->generate(Argument::type('string'))->willReturn('test');
        $this->container->get(UrlHelper::class)
            ->willReturn($urlHelper->reveal());

        $this->container->get('config')->willReturn(['APP_DOMAIN' => 'localhost']);
    }

    public function testLoginHandlerFactory()
    {
        $factory = new InsalesSettingsServiceFactory();
        $newClass = $factory($this->container->reveal(), InsalesSettingsService::class);
        $this->assertInstanceOf(InsalesSettingsService::class, $newClass);
    }
}
