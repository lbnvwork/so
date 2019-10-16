<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\LandingApiFactory;
use App\Handler\LandingApiHandler;
use App\Helper\UrlHelper;
use App\Service\RecaptchaService;
use App\Service\SendMail;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class LandingApiHandlerFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get(RecaptchaService::class)->willReturn($this->prophesize(RecaptchaService::class));
        $this->container->get(SendMail::class)->willReturn($this->prophesize(SendMail::class));
        $this->container->get(UrlHelper::class)->willReturn($this->prophesize(UrlHelper::class));
        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
    }

    public function testFactory()
    {
        $factory = new LandingApiFactory();

        $newClass = $factory($this->container->reveal());

        $this->assertInstanceOf(LandingApiHandler::class, $newClass);
    }
}