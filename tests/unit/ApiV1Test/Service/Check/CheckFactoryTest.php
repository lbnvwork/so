<?php

namespace ApiV1Test\Service\Check;

use ApiV1\Service\Check\CheckFactory;
use ApiV1\Service\Check\Correction;
use ApiV1\Service\Check\Normal;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class CheckFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
    }

    public function testNormalCheck()
    {
        $factory = new CheckFactory();

        $newClass = $factory($this->container->reveal(), Normal::class);

        $this->assertInstanceOf(Normal::class, $newClass);
    }

    public function testCorrectionCheck()
    {
        $factory = new CheckFactory();

        $newClass = $factory($this->container->reveal(), Correction::class);

        $this->assertInstanceOf(Correction::class, $newClass);
    }
}
