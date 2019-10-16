<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 19.07.19
 * Time: 14:12
 */

namespace ApiInsalesTest\Handler;

use ApiInsales\Handler\InstallHandler;
use ApiInsales\Handler\InstallHandlerFactory;
use ApiInsales\Service\InsalesSettingsService;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Interop\Container\ContainerInterface;
use Prophecy\Prophecy\ObjectProphecy;

class InstallHandlerFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));;
        $this->container->get(InsalesSettingsService::class)->willReturn($this->prophesize(InsalesSettingsService::class));
    }

    public function testInstallHandlerFactory()
    {
        $factory = new InstallHandlerFactory();

        $newClass = $factory($this->container->reveal(), InstallHandler::class);

        $this->assertInstanceOf(InstallHandler::class, $newClass);
    }
}
