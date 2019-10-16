<?php

declare(strict_types=1);

namespace AuthTest\Service;

use Auth\Service\SendMailFactory;
use Auth\Service\SendMail;
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