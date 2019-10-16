<?php

declare(strict_types=1);

namespace AppTest\Service;

use App\Service\RecaptchaService;
use App\Service\RecaptchaServiceFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class RecaptchaServiceFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory()
    {
        $factory = new RecaptchaServiceFactory();

        $newClass = $factory($this->container->reveal());

        $this->assertInstanceOf(RecaptchaService::class, $newClass);
    }
}