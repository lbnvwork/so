<?php

declare(strict_types=1);

namespace AuthTest\Middleware;

use Auth\Middleware\AuthenticationMiddleware;
use Auth\Middleware\AuthenticationMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Authentication\AuthenticationInterface;
use Zend\Expressive\Authentication\Exception\InvalidConfigException;
use Zend\Expressive\Template\TemplateRendererInterface;

class AuthenticationMiddlewareFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryWithoutAuthentication()
    {
        $factory = new AuthenticationMiddlewareFactory();
        $this->container->has(AuthenticationInterface::class)->willReturn(false);

        $this->assertInstanceOf(AuthenticationMiddlewareFactory::class, $factory);

        $this->expectException(InvalidConfigException::class);
        $factory($this->container->reveal());
    }

    public function testFactory()
    {
        $this->container->has(AuthenticationInterface::class)->willReturn(true);
        $this->container
            ->get(AuthenticationInterface::class)
            ->willReturn($this->prophesize(AuthenticationInterface::class));
        $this->container->has(TemplateRendererInterface::class)->willReturn(true);
        $this->container
            ->get(TemplateRendererInterface::class)
            ->willReturn($this->prophesize(TemplateRendererInterface::class));

        $factory = new AuthenticationMiddlewareFactory();

        $newClass = $factory($this->container->reveal());

        $this->assertInstanceOf(AuthenticationMiddleware::class, $newClass);
    }
}