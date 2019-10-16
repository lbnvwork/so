<?php

declare(strict_types=1);

namespace AuthTest\Action;

use App\Helper\UrlHelper;
use Auth\Action\RollbackAction;
use Auth\Action\RollbackActionFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class RollbackActionFactoryTest extends TestCase
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
        $factory = new RollbackActionFactory();

        $newClass = $factory($this->container->reveal(), RollbackAction::class);

        $this->assertInstanceOf(RollbackAction::class, $newClass);
    }
}