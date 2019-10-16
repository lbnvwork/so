<?php

namespace ApiInsalesTest\Handler;

use ApiInsales\Handler\LoginHandler;
use ApiInsales\Handler\LoginHandlerFactory;
use ApiInsales\Service\InsalesSettingsService;
use ApiInsales\Service\LoginService;
use App\Helper\UrlHelper;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Zend\Expressive\Template\TemplateRendererInterface;

class LoginHandlerFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get('doctrine.entity_manager.orm_default')
            ->willReturn($this->prophesize(EntityManager::class));

        $this->container->get(InsalesSettingsService::class)
            ->willReturn($this->prophesize(InsalesSettingsService::class));

        $this->container->get(UrlHelper::class)
            ->willReturn($this->prophesize(UrlHelper::class));

        $this->container->get(TemplateRendererInterface::class)
            ->willReturn($this->prophesize(TemplateRendererInterface::class));

        $this->container->get(LoginService::class)
            ->willReturn($this->prophesize(LoginService::class));

        $this->container->get('config')->willReturn(['APP_DOMAIN' => 'localhost']);
    }

    public function testLoginHandlerFactory()
    {
        $factory = new LoginHandlerFactory();
        $newClass = $factory($this->container->reveal(), LoginHandler::class);
        $this->assertInstanceOf(LoginHandler::class, $newClass);
    }
}
