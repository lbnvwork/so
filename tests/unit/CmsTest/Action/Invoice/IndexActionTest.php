<?php

namespace CmsTest\Action\Invoice;

use App\Helper\UrlHelper;
use Cms\Action\Invoice\IndexAction;
use Cms\Action\Invoice\IndexActionFactory;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Office\Service\SendMail;
use PHPUnit\Framework\TestCase;
use Zend\Expressive\Template\TemplateRendererInterface;

class IndexActionTest extends TestCase
{
    /**
     * @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    private $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
        $this->container->has(TemplateRendererInterface::class)->willReturn(true);
        $this->container->get(TemplateRendererInterface::class)->willReturn($this->prophesize(TemplateRendererInterface::class));
        $this->container->get(UrlHelper::class)->willReturn($this->prophesize(UrlHelper::class));
        $this->container->get(SendMail::class)->willReturn($this->prophesize(SendMail::class));
    }

    public function testCanCreateIndexAction(): void
    {
        $factory = new IndexActionFactory();
        $newClass = $factory($this->container->reveal(), IndexAction::class);
        $this->assertInstanceOf(IndexAction::class, $newClass);
    }
}
