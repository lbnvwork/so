<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 14.08.19
 * Time: 10:19
 */

namespace ApiInsalesTest\Handler;


use ApiInsales\Handler\AutologinHandler;
use ApiInsales\Handler\AutologinHandlerFactory;
use ApiInsales\Service\InsalesSettingsService;
use ApiInsales\Service\LoginService;
use App\Helper\UrlHelper;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Zend\Expressive\Template\TemplateRendererInterface;

class AutologinHandlerFactoryTest extends TestCase
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
    }

    public function testAutologinHandlerFactory()
    {
        $factory = new AutologinHandlerFactory();
        $newClass = $factory($this->container->reveal(), AutologinHandler::class);
        $this->assertInstanceOf(AutologinHandler::class, $newClass);
    }
}