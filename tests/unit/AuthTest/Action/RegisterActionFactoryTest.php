<?php

declare(strict_types=1);

namespace AuthTest\Action;

use App\Helper\UrlHelper;
use Auth\Action\RegisterAction;
use Auth\Action\RegisterActionFactory;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Authentication\UserRepositoryInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class RegisterActionFactoryTest extends TestCase
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

        $factory = new RegisterActionFactory();

        $newClass = $factory($this->container->reveal(), RegisterAction::class);

        $this->assertInstanceOf(RegisterAction::class, $newClass);
    }
}