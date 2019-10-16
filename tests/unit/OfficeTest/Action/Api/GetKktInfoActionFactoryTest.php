<?php
declare(strict_types=1);

namespace OfficeTest\Action\Api;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Office\Action\Api\GetkktInfoAction;
use Office\Action\Api\GetKktInfoActionFactory;
use Office\Service\KktService;
use PHPUnit\Framework\TestCase;

class GetKktInfoActionFactoryTest extends TestCase
{
    public function testFactory()
    {
        $container = $this->prophesize(ContainerInterface::class);

        $container->get(KktService::class)->willReturn($this->prophesize(KktService::class));
        $container->get('doctrine.entity_manager.orm_default')->willReturn($this->prophesize(EntityManager::class));

        $factory = new GetKktInfoActionFactory();

        $newClass = $factory($container->reveal(), GetkktInfoAction::class);

        $this->assertInstanceOf(GetkktInfoAction::class, $newClass);
    }
}
