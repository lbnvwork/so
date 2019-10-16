<?php

declare(strict_types=1);

namespace AppTest\Service;

use App\Service\SendMail;
use App\Service\SendMailFactory;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Authorization\Exception\InvalidConfigException;

class SendMailFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
        $this->container->get('config')->willReturn([]);
    }

    public function testFactoryWithoutEmailConfig()
    {
        $factory = new SendMailFactory();

        $this->expectException(InvalidConfigException::class);
        $factory($this->container->reveal());
    }

    public function testFactory()
    {
        $this->container->get('config')->willReturn(['sendMail' => []]);

        $factory = new SendMailFactory();

        $newClass = $factory($this->container->reveal());

        $this->assertInstanceOf(SendMail::class, $newClass);
    }
}