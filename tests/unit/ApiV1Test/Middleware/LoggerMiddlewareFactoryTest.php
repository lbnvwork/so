<?php

namespace ApiV1Test\Middleware;

use ApiV1\Middleware\LoggerMiddleware;
use ApiV1\Middleware\LoggerMiddlewareFactory;
use Interop\Container\ContainerInterface;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class LoggerMiddlewareFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class)->reveal();
    }

    /**
     * @param $object
     * @param $propertyName
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function invokeProperty(&$object, $propertyName)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    public function testApi()
    {
        $factory = new LoggerMiddlewareFactory();

        $logger = $factory($this->container, LoggerMiddlewareFactory::API_V1_NAME);
        $this->assertInstanceOf(LoggerMiddleware::class, $logger);

        /** @var Logger $property */
        $property = $this->invokeProperty($logger, 'logger');
        $this->assertEquals('api', $property->getName());
    }

    public function testApiAtol()
    {
        $factory = new LoggerMiddlewareFactory();

        $logger = $factory($this->container, LoggerMiddlewareFactory::API_ATOL_V1_NAME);
        $this->assertInstanceOf(LoggerMiddleware::class, $logger);

        /** @var Logger $property */
        $property = $this->invokeProperty($logger, 'logger');
        $this->assertEquals('apiatol', $property->getName());
    }
}
