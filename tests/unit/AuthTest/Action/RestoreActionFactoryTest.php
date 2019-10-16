<?php

declare(strict_types=1);

namespace AuthTest\Action;

use App\Helper\UrlHelper;
use Auth\Action\RestoreAction;
use Auth\Action\RestoreActionFactory;
use Auth\Service\SendMail;
use Auth\UserRepository\Database;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class RestoreActionFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get(UrlHelper::class)->willReturn($this->prophesize(UrlHelper::class));
        $this->container->get(Database::class)->willReturn($this->prophesize(Database::class));
        $this->container->get(SendMail::class)->willReturn($this->prophesize(SendMail::class));
        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
    }

    public function testFactory()
    {
        $this->container
            ->get(TemplateRendererInterface::class)
            ->willReturn($this->prophesize(TemplateRendererInterface::class));

        $factory = new RestoreActionFactory();

        $newClass = $factory($this->container->reveal(), RestoreAction::class);

        $this->assertInstanceOf(RestoreAction::class, $newClass);
    }
}