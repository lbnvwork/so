<?php

declare(strict_types=1);

namespace PermissionTest\Middleware;

use App\Helper\UrlHelper;
use Permission\Middleware\PermissionMiddleware;
use Permission\Middleware\PermissionMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\ServiceManager;

class PermissionMiddlewareFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ServiceManager::class);
        $this->container->get(UrlHelper::class)->willReturn($this->prophesize(UrlHelper::class));
        $this->container->setService(Argument::any(), Argument::any())->willReturn();
    }

    public function testFactoryWithoutRoles()
    {
        $factory = new PermissionMiddlewareFactory();
        $this->container->get('config')->willReturn([]);

        $this->assertInstanceOf(PermissionMiddlewareFactory::class, $factory);

        $this->expectException(\Exception::class);
        $factory($this->container->reveal());
    }

    public function testFactoryWithoutPermissions()
    {
        $factory = new PermissionMiddlewareFactory();

        $this->container->get('config')->willReturn(['rbac' => ['roles' => []]]);

        $this->assertInstanceOf(PermissionMiddlewareFactory::class, $factory);

        $this->expectException(\Exception::class);
        $factory($this->container->reveal());
    }

    public function testFactory()
    {
        $this->container->get('config')->willReturn(
            [
                'rbac' => [
                    'roles'       => [
                        'user'  => [],
                        'guest' => ['user'],
                    ],
                    'permissions' => [
                        'guest' => [
                            'home',
                        ],
                    ],
                    'asserts'     => [],
                ],
            ]
        );

        $factory = new PermissionMiddlewareFactory();

        $newClass = $factory($this->container->reveal());

        $this->assertInstanceOf(PermissionMiddleware::class, $newClass);
    }
}