<?php

declare(strict_types=1);

namespace ApiV1Test\Action;

use ApiV1\Action\RegisterCheckAction;
use ApiV1\Action\RegisterCheckActionFactory;
use ApiV1\Action\ReportAction;
use ApiV1\Service\Check\Correction;
use ApiV1\Service\Check\Normal;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class RegisterCheckActionFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
        $this->container->get(Normal::class)->willReturn($this->prophesize(Normal::class));
        $this->container->get(Correction::class)->willReturn($this->prophesize(Correction::class));
    }

    public function testRegisterCheckActionFactory()
    {
        $factory = new RegisterCheckActionFactory();

        $newClass = $factory($this->container->reveal(), RegisterCheckAction::class);

        $this->assertInstanceOf(RegisterCheckAction::class, $newClass);
    }

    public function testReportActionFactory()
    {
        $factory = new RegisterCheckActionFactory();

        $newClass = $factory($this->container->reveal(), ReportAction::class);

        $this->assertInstanceOf(ReportAction::class, $newClass);
    }
}