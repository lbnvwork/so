<?php

declare(strict_types=1);

namespace AppTest\Service;

use App\Service\LoggerFactory;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class LoggerFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    protected function tearDown(): void
    {
        exec('rm -rf '.ROOT_PATH.'test/data');
    }

    public function testFactory()
    {
        $factory = new LoggerFactory();

        $newClass = $factory($this->container->reveal(), Logger::class);

        $this->assertInstanceOf(Logger::class, $newClass);
    }
}