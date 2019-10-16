<?php

namespace CmsTest\Action\Kkt;

use ApiV1\Service\Umka\UmkaApi;
use App\Helper\UrlHelper;
use Cms\Action\Kkt\CloseFnAction;
use Cms\Action\Kkt\CloseShiftAction;
use Cms\Action\Kkt\CloseActionFactory;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class CloseActionFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
        $this->container->get(UrlHelper::class)->willReturn($this->prophesize(UrlHelper::class));
        $this->container->get(UmkaApi::class)->willReturn($this->prophesize(UmkaApi::class));
    }

    public function test__invoke()
    {
        $factory = new CloseActionFactory();

        $newClass = $factory($this->container->reveal(), CloseShiftAction::class);

        $this->assertInstanceOf(CloseShiftAction::class, $newClass);
    }

    public function testCloseFnFactory()
    {
        $factory = new CloseActionFactory();

        $newClass = $factory($this->container->reveal(), CloseFnAction::class);

        $this->assertInstanceOf(CloseFnAction::class, $newClass);
    }
}
