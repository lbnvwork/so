<?php

declare(strict_types=1);

namespace AppTest\Middleware;

use App\Middleware\FlashMessageFactory;
use App\Middleware\FlashMessageMiddleware;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class FlashMessageFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryWithTemplate()
    {
        $this->container->has(TemplateRendererInterface::class)->willReturn(true);
        $this->container
            ->get(TemplateRendererInterface::class)
            ->willReturn($this->prophesize(TemplateRendererInterface::class));

        $factory = new FlashMessageFactory();

        $newClass = $factory($this->container->reveal(), FlashMessageMiddleware::class);

        $this->assertInstanceOf(FlashMessageMiddleware::class, $newClass);
    }
}