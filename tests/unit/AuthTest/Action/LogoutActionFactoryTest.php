<?php

declare(strict_types=1);

namespace AuthTest\Action;

use App\Helper\UrlHelper;
use Auth\Action\LogoutAction;
use Auth\Action\LogoutActionFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class LogoutActionFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get(UrlHelper::class)->willReturn($this->prophesize(UrlHelper::class));
    }

    public function testFactory()
    {
        $factory = new LogoutActionFactory();

        $newClass = $factory($this->container->reveal(), LogoutAction::class);

        $this->assertInstanceOf(LogoutAction::class, $newClass);
    }
}