<?php
declare(strict_types=1);

namespace OfficeTest\Service;

use ApiV1\Service\Umka\UmkaApi;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Office\Service\KktService;
use Office\Service\KktServiceFactory;
use Office\Service\Umka;
use PHPUnit\Framework\TestCase;

class KktServiceFactoryTest extends TestCase
{
    public function testFactory()
    {
        $container = $this->prophesize(ContainerInterface::class);

        $container->get(Umka::class)->willReturn($this->prophesize(Umka::class));
        $container->get(UmkaApi::class)->willReturn($this->prophesize(UmkaApi::class));
        $container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));

        $factory = new KktServiceFactory();

        $newClass = $factory($container->reveal(), KktService::class);

        $this->assertInstanceOf(KktService::class, $newClass);
    }
}
