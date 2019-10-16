<?php

namespace ApiV1Test\Service;

use ApiV1\Service\TokenService;
use Doctrine\ORM\EntityManager;
use Office\Entity\ApiKey;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class TokenServiceTest extends TestCase
{
    /** @var EntityManager|ObjectProphecy */
    private $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->prophesize(EntityManager::class)->reveal();

//        $this->entityManager->persist(Argument::any());
    }

    public function testCheckTokenNew()
    {
        $apiKey = new ApiKey();
        $class = new TokenService($this->entityManager);
        $this->assertTrue($class->checkToken($apiKey));
        $this->assertNotNull($apiKey->getToken());
        $this->assertNotNull($apiKey->getDateExpiredToken());
        $this->assertEquals(0, $apiKey->getDateExpiredToken()->diff(new \DateTime())->d);
        $this->assertEquals(23, $apiKey->getDateExpiredToken()->diff(new \DateTime())->h);
    }

    public function testCheckTokenNew1H()
    {
        $apiKey = new ApiKey();
        $apiKey->setDateExpiredToken((new \DateTime())->add(new \DateInterval('PT1H')));

        $class = new TokenService($this->entityManager);
        $this->assertTrue($class->checkToken($apiKey));
        $this->assertNotNull($apiKey->getToken());
        $this->assertNotNull($apiKey->getDateExpiredToken());
        $this->assertEquals(0, $apiKey->getDateExpiredToken()->diff(new \DateTime())->d);
        $this->assertEquals(23, $apiKey->getDateExpiredToken()->diff(new \DateTime())->h);
    }

    public function testCheckTokenOld1H()
    {
        $apiKey = new ApiKey();
        $apiKey->setDateExpiredToken((new \DateTime())->add(new \DateInterval('PT1H1M')));

        $class = new TokenService($this->entityManager);
        $this->assertFalse($class->checkToken($apiKey));
        $this->assertNull($apiKey->getToken());
        $this->assertNotNull($apiKey->getDateExpiredToken());
        $this->assertEquals(0, $apiKey->getDateExpiredToken()->diff(new \DateTime())->d);
        $this->assertEquals(1, $apiKey->getDateExpiredToken()->diff(new \DateTime())->h);
    }

    public function testCheckTokenOld()
    {
        $apiKey = new ApiKey();
        $apiKey->setDateExpiredToken((new \DateTime())->add(new \DateInterval('P1D')));

        $class = new TokenService($this->entityManager);
        $this->assertFalse($class->checkToken($apiKey));
        $this->assertNull($apiKey->getToken());
        $this->assertNotNull($apiKey->getDateExpiredToken());
        $this->assertEquals(0, $apiKey->getDateExpiredToken()->diff(new \DateTime())->d);
        $this->assertEquals(23, $apiKey->getDateExpiredToken()->diff(new \DateTime())->h);
    }
}
