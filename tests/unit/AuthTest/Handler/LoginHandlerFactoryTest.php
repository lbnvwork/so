<?php

declare(strict_types=1);

namespace AuthTest\Handler;

use App\Helper\UrlHelper;
use Auth\Handler\LoginHandler;
use Auth\Handler\LoginHandlerFactory;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Authentication\UserRepositoryInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class LoginHandlerFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get(UrlHelper::class)->willReturn($this->prophesize(UrlHelper::class));
        $this->container->get(UserRepositoryInterface::class)->willReturn($this->prophesize(UserRepositoryInterface::class));
        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
    }

    public function testFactory()
    {
        $this->container
            ->get(TemplateRendererInterface::class)
            ->willReturn($this->prophesize(TemplateRendererInterface::class));

        $factory = new LoginHandlerFactory();

        $newClass = $factory($this->container->reveal());

        $this->assertInstanceOf(LoginHandler::class, $newClass);
    }
}