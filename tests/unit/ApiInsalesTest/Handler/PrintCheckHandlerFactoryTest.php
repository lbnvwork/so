<?php

namespace ApiInsalesTest\Handler;

use ApiInsales\Handler\LoginHandler;
use ApiInsales\Handler\PrintCheckHandler;
use ApiInsales\Handler\PrintCheckHandlerFactory;
use ApiInsales\Service\WebhookParserService;
use ApiV1\Service\Check\Normal;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class PrintCheckHandlerFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    public const API_INSALES_NAME = 'insales';

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->container->get('doctrine.entity_manager.orm_default')
            ->willReturn($this->prophesize(EntityManager::class));

        $this->container->get(WebhookParserService::class)
            ->willReturn($this->prophesize(WebhookParserService::class));

        $this->container->get(Normal::class)
            ->willReturn($this->prophesize(Normal::class));

        $filename = self::API_INSALES_NAME;

        $logger = new Logger($filename);
        $logger->pushHandler(new StreamHandler(ROOT_PATH.'data/'.$filename.'-'.date('Y-m-d').'.log'));
    }

    public function testPrintCheckHandlerFactory()
    {
        $factory = new PrintCheckHandlerFactory();
        $newClass = $factory($this->container->reveal(), PrintCheckHandler::class);
        $this->assertInstanceOf(PrintCheckHandler::class, $newClass);
    }
}
