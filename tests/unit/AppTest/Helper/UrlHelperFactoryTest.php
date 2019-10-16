<?php

declare(strict_types=1);

namespace AppTest\Helper;

use App\Helper\UrlHelper;
use App\Helper\UrlHelperFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Helper\Exception\MissingRouterException;
use Zend\Expressive\Router\RouterInterface;

class UrlHelperFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryWithoutRouter()
    {
        $factory = new UrlHelperFactory();
        $this->container->has(RouterInterface::class)->willReturn(false);

        $this->assertInstanceOf(UrlHelperFactory::class, $factory);

        $this->expectException(MissingRouterException::class);
        $factory($this->container->reveal());
    }

    public function testFactoryWithRouter()
    {
        $this->container->has(RouterInterface::class)->willReturn(true);
        $this->container
            ->get(RouterInterface::class)
            ->willReturn($this->prophesize(RouterInterface::class));

        $factory = new UrlHelperFactory();

        $newClass = $factory($this->container->reveal());

        $this->assertInstanceOf(UrlHelper::class, $newClass);
    }
}