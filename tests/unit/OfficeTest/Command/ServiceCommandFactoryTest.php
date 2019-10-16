<?php

namespace OfficeTest\Command;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Office\Command\CheckUseKktCommand;
use Office\Command\CheckUserPaymentsCommand;
use Office\Command\PaymentKktCommand;
use Office\Command\ServiceCommandFactory;
use Office\Service\SendMail;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class ServiceCommandFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get(SendMail::class)->willReturn($this->prophesize(SendMail::class));
        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
    }

    public function testCheckUseKkt()
    {
        $factory = new ServiceCommandFactory();

        $newClass = $factory($this->container->reveal(), CheckUseKktCommand::class);

        $this->assertInstanceOf(CheckUseKktCommand::class, $newClass);
    }

    public function testCheckUserPayments()
    {
        $factory = new ServiceCommandFactory();

        $newClass = $factory($this->container->reveal(), CheckUserPaymentsCommand::class);

        $this->assertInstanceOf(CheckUserPaymentsCommand::class, $newClass);
    }

    public function testPaymentKkt()
    {
        $factory = new ServiceCommandFactory();

        $newClass = $factory($this->container->reveal(), PaymentKktCommand::class);

        $this->assertInstanceOf(PaymentKktCommand::class, $newClass);
    }
}
