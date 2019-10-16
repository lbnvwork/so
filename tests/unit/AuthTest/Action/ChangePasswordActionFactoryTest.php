<?php

declare(strict_types=1);

namespace AuthTest\Action;

use App\Helper\UrlHelper;
use Auth\Action\ChangePasswordAction;
use Auth\Action\ChangePasswordActionFactory;
use Auth\UserRepository\Database;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class ChangePasswordActionFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get(UrlHelper::class)->willReturn($this->prophesize(UrlHelper::class));
        $this->container->get(Database::class)->willReturn($this->prophesize(Database::class));
        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
    }

    public function testFactoryWithTemplate()
    {
        $this->container->has(TemplateRendererInterface::class)->willReturn(true);
        $this->container
            ->get(TemplateRendererInterface::class)
            ->willReturn($this->prophesize(TemplateRendererInterface::class));

        $factory = new ChangePasswordActionFactory();

        $newClass = $factory($this->container->reveal(), ChangePasswordAction::class);

        $this->assertInstanceOf(ChangePasswordAction::class, $newClass);
    }
}