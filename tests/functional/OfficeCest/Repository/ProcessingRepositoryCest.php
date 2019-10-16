<?php

namespace OfficeCest\Repository;

use App\Service\DateTime;
use Doctrine\ORM\EntityManager;
use Office\Entity\Company;
use Office\Entity\Processing;
use Office\Entity\Shop;
use Office\Repository\ProcessingRepository;

class ProcessingRepositoryCest
{
    /**
     * @var Shop
     */
    private $testShop;

    /**
     * @var Shop
     */
    private $shop;

    /**
     * @var Shop
     */
    private $singleShop;

    /**
     * @var Shop
     */
    private $shop2;

    public function _before(\FunctionalTester $tester): void
    {
        $tester->haveInRepository(Company::class, ['title' => 'test company']);
        $id = $tester->haveInRepository(
            Shop::class, [
                'title'    => 'test shop',
                'isTest'   => true,
                'isSingle' => false,
            ]
        );
        $this->testShop = $tester->grabEntityFromRepository(Shop::class, ['id' => $id]);
        $id = $tester->haveInRepository(
            Shop::class, [
                'title'    => 'shop',
                'isTest'   => false,
                'isSingle' => false,
            ]
        );
        $this->shop = $tester->grabEntityFromRepository(Shop::class, ['id' => $id]);
        $id = $tester->haveInRepository(
            Shop::class, [
                'title'    => 'shop 2',
                'isTest'   => false,
                'isSingle' => false,
            ]
        );
        $this->shop2 = $tester->grabEntityFromRepository(Shop::class, ['id' => $id]);

        $id = $tester->haveInRepository(
            Shop::class, [
                'title'    => 'single shop',
                'isTest'   => false,
                'isSingle' => true,
            ]
        );
        $this->singleShop = $tester->grabEntityFromRepository(Shop::class, ['id' => $id]);

        $tester->haveInRepository(
            Processing::class, [
                'shop'      => $this->testShop,
                'datetime'  => new DateTime(),
                'status'    => Processing::STATUS_ACCEPT,
                'operation' => Processing::OPERATION_SELL,
                'rawData'   => 'for test shop',
            ]
        );
        $tester->haveInRepository(
            Processing::class, [
                'shop'      => $this->shop,
                'datetime'  => new DateTime(),
                'status'    => Processing::STATUS_ACCEPT,
                'operation' => Processing::OPERATION_SELL,
                'rawData'   => 'for shop',
            ]
        );
        $tester->haveInRepository(
            Processing::class, [
                'shop'      => $this->shop,
                'datetime'  => new DateTime(),
                'status'    => Processing::STATUS_PREPARE,
                'operation' => Processing::OPERATION_SELL,
                'rawData'   => 'for shop',
            ]
        );
        $tester->haveInRepository(
            Processing::class, [
                'shop'      => $this->singleShop,
                'datetime'  => new DateTime(),
                'status'    => Processing::STATUS_ACCEPT,
                'operation' => Processing::OPERATION_SELL,
                'rawData'   => 'for single shop',
            ]
        );
    }

    public function testGetProcessingByStatus(\FunctionalTester $tester): void
    {
        /** @var EntityManager $entityManager */
        $entityManager = $tester->getEntityManager();
        /** @var ProcessingRepository $repository */
        $repository = $entityManager->getRepository(Processing::class);

        $entityes = $repository->getProcessing(Processing::STATUS_ACCEPT);
        $tester->assertCount(1, $entityes);
        $entity = current($entityes);
        $tester->assertEquals('for shop', $entity->getRawData());
    }

    public function testGetProcessingForTestShop(\FunctionalTester $tester): void
    {
        /** @var EntityManager $entityManager */
        $entityManager = $tester->getEntityManager();
        /** @var ProcessingRepository $repository */
        $repository = $entityManager->getRepository(Processing::class);

        $entityes = $repository->getProcessing(Processing::STATUS_ACCEPT, true);
        $tester->assertCount(1, $entityes);
        $entity = current($entityes);
        $tester->assertEquals('for test shop', $entity->getRawData());
    }

    public function testGetProcessingForSingleShop(\FunctionalTester $tester): void
    {
        /** @var EntityManager $entityManager */
        $entityManager = $tester->getEntityManager();
        /** @var ProcessingRepository $repository */
        $repository = $entityManager->getRepository(Processing::class);

        $entityes = $repository->getProcessing(Processing::STATUS_ACCEPT, false, null, true);
        $tester->assertCount(1, $entityes);
        $entity = current($entityes);
        $tester->assertEquals('for single shop', $entity->getRawData());
    }

    public function testGetProcessingForCurrentShop(\FunctionalTester $tester): void
    {
        /** @var EntityManager $entityManager */
        $entityManager = $tester->getEntityManager();
        /** @var ProcessingRepository $repository */
        $repository = $entityManager->getRepository(Processing::class);

        $entityes = $repository->getProcessing(Processing::STATUS_ACCEPT, false, $this->shop2);
        $tester->assertCount(0, $entityes);
    }

    public function testGetProcessingForSingleAndTestShop(\FunctionalTester $tester): void
    {
        /** @var EntityManager $entityManager */
        $entityManager = $tester->getEntityManager();
        /** @var ProcessingRepository $repository */
        $repository = $entityManager->getRepository(Processing::class);

        $entityes = $repository->getProcessing(Processing::STATUS_ACCEPT, true, null, true);
        $tester->assertCount(0, $entityes);
    }

    public function testGetProcessingForCurrentShopNotSingle(\FunctionalTester $tester): void
    {
        /** @var EntityManager $entityManager */
        $entityManager = $tester->getEntityManager();
        /** @var ProcessingRepository $repository */
        $repository = $entityManager->getRepository(Processing::class);

        $entityes = $repository->getProcessing(Processing::STATUS_ACCEPT, false, $this->shop, true);
        $tester->assertCount(0, $entityes);
    }
}
