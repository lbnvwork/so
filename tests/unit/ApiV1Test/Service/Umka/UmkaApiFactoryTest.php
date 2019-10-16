<?php

declare(strict_types=1);

namespace ApiV1Test\Service\Umka;

use ApiV1\Service\Umka\UmkaApi;
use ApiV1\Service\Umka\UmkaApiFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Interop\Container\ContainerInterface;

class UmkaApiFactoryTest extends TestCase
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

        $this->container->get('doctrine.entity_manager.orm_default')->willReturn($entityManager);
    }

    public function testFactory()
    {
        $factory = new UmkaApiFactory();

        $newClass = $factory($this->container->reveal(), UmkaApi::class);

        $this->assertInstanceOf(UmkaApi::class, $newClass);
    }
}