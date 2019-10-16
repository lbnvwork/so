<?php

declare(strict_types=1);

namespace AuthTest\Action;

use App\Helper\UrlHelper;
use Auth\Action\ConfirmAction;
use Auth\Action\ConfirmActionFactory;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class ConfirmActionFactoryTest extends TestCase
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
        $factory = new ConfirmActionFactory();

        $newClass = $factory($this->container->reveal());

        $this->assertInstanceOf(ConfirmAction::class, $newClass);
    }
}