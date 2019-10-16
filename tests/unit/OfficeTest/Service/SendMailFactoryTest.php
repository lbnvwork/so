<?php

declare(strict_types=1);

namespace OfficeTest\Service;

use App\Helper\UrlHelper;
use Doctrine\ORM\EntityManager;
use Office\Service\SendMailFactory;
use Office\Service\SendMail;
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
        $this->container->get(UrlHelper::class)->willReturn($this->prophesize(UrlHelper::class));
        $this->container->get('config')->willReturn([]);
    }

    public function testFactoryWithoutConfig()
    {
        $factory = new SendMailFactory();

        $this->expectException(InvalidConfigException::class);
        $factory($this->container->reveal());
    }

    public function testFactory()
    {
        $factory = new SendMailFactory();
        $this->container->get('config')->willReturn(
            [
                'sendMail' => [],
            ]
        );
        $newClass = $factory($this->container->reveal());

        $this->assertInstanceOf(SendMail::class, $newClass);
    }
}