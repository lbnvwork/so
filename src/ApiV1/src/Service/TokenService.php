<?php
declare(strict_types=1);

namespace ApiV1\Service;

use Doctrine\ORM\EntityManager;
use Office\Entity\ApiKey;

/**
 * Class TokenService
 *
 * @package ApiV1\Service
 */
class TokenService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * TokenService constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param ApiKey $apiKey
     *
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function checkToken(ApiKey $apiKey): bool
    {
        $newKey = false;
        if ($apiKey->getDateExpiredToken() === null) {
            $newKey = true;
        } else {
            $diff = $apiKey->getDateExpiredToken()->diff(new \DateTime());
            if (($diff->invert === 0) || ($diff->invert === 1 && $diff->h < 1)) {
                $newKey = true;
            }
        }

        if ($newKey) {
            $apiKey->setToken(uniqid((string)time()));
            $apiKey->setDateExpiredToken((new \DateTime())->add(new \DateInterval('P1D')));
            $this->entityManager->persist($apiKey);
            $this->entityManager->flush();
        }

        return $newKey;
    }
}
