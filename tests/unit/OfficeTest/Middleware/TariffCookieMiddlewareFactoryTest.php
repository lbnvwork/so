<?php

declare(strict_types=1);

namespace OfficeTest\Middleware;

use App\Helper\UrlHelper;
use Doctrine\ORM\EntityManager;
use Office\Middleware\TariffCookieMiddleware;
use Office\Middleware\TariffCookieMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class TariffCookieMiddlewareFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get(UrlHelper::class)->willReturn($this->prophesize(UrlHelper::class));
        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
    }

    public function testFactory()
    {
        $factory = new TariffCookieMiddlewareFactory();

        $newClass = $factory($this->container->reveal());

        $this->assertInstanceOf(TariffCookieMiddleware::class, $newClass);
    }
}