<?php

declare(strict_types=1);

namespace OfficeTest\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Office\Service\Umka;
use Office\Service\UmkaFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class UmkaFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $entityManager = $this->prophesize(EntityManager::class);

        $shippingMethodRepository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->setMethods(['createQueryBuilder'])
            ->getMock();

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(['getResult'])
            ->getMockForAbstractClass();

        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuery'])
            ->getMock();

        $queryBuilder->method('getQuery')->willReturn($query);

        $shippingMethodRepository->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $entityManager->getRepository(Argument::any())
            ->willReturn($shippingMethodRepository);

        $this->container->get(EntityManager::class)->willReturn($entityManager);
    }

    public function testFactory()
    {
        $factory = new UmkaFactory();
        $newClass = $factory($this->container->reveal(), Umka::class);

        $this->assertInstanceOf(Umka::class, $newClass);
    }
}