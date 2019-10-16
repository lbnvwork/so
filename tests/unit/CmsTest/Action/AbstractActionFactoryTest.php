<?php
declare(strict_types=1);

namespace CmsTest\Action;

use App\Helper\UrlHelper;
use Cms\Action\AbstractActionFactory;
use Cms\ConfigProvider;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\Expressive\Template\TemplateRendererInterface;

class AbstractActionFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    private $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));
        $this->container->get(TemplateRendererInterface::class)->willReturn($this->prophesize(TemplateRendererInterface::class));
        $this->container->get(UrlHelper::class)->willReturn($this->prophesize(UrlHelper::class));
    }

    /**
     * @param string $className
     *
     * @dataProvider classProvider
     */
    public function testCanCreateActions(string $className): void
    {
        $factory = new AbstractActionFactory();
        $newClass = $factory($this->container->reveal(), $className);
        $this->assertInstanceOf($className, $newClass);
    }

    public function classProvider(): array
    {
        $config = new ConfigProvider();
        $factories = $config->getDependencies()['factories'];

        $abstractFactories = [];

        foreach ($factories as $class => $factory) {
            if ($factory === AbstractActionFactory::class) {
                $abstractFactories[] = [$class];
            }
        }

        return $abstractFactories;
    }
}
