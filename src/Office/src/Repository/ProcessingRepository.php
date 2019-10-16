<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 07.02.19
 * Time: 16:36
 */

namespace Office\Repository;

use App\Service\DateTime;
use Doctrine\ORM\EntityRepository;
use Office\Entity\Kkt;
use Office\Entity\Processing;
use Office\Entity\Shop;

/**
 * Class ProcessingRepository
 *
 * @package Office\Repository
 */
class ProcessingRepository extends EntityRepository
{
    /**
     * @param Kkt $kkt
     *
     * @return int|string|null
     * @throws \Exception
     */
    public function getPrevMonthTurnoverKkt(Kkt $kkt)
    {
        $currDate = new DateTime('last day of last month 23:59:59');
        $firstDay = new DateTime('first day of last month 00:00:00');

        $turnover = $this->createQueryBuilder('k')
            ->select('SUM(k.sum)')
            ->where('k.kkt = :kkt AND k.datetime BETWEEN :firstDay AND :currDay')
            ->setParameters(
                [
                    'kkt'      => $kkt,
                    'currDay'  => $currDate,
                    'firstDay' => $firstDay,
                ]
            )
            ->getQuery()
            ->getOneOrNullResult();


        return current($turnover);
    }

    /**
     * @param int $status
     * @param int|null $limit
     * @param Shop|null $shop
     *
     * @return mixed
     */
    public function getProcessingByStatus(int $status, ?int $limit = null, ?Shop $shop = null)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', $status);

        if ($shop !== null) {
            $qb->andWhere('p.shop = :shop')
                ->setParameter('shop', $shop);
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Получить список чеков
     *
     * @param int $status
     * @param bool $isTest
     * @param Shop|null $shop
     * @param bool $isSingle
     * @param int $_limit
     *
     * @return Processing[]
     */
    public function getProcessing(int $status, bool $isTest = false, Shop $shop = null, bool $isSingle = false, int $_limit = 100)
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.shop', 's')
            ->where('p.status = :status')
            ->setParameter('status', $status)
            ->andWhere('s.isTest =:isTest')
            ->setParameter('isTest', $isTest)
            ->andWhere('s.isSingle =:isSingle')
            ->setParameter('isSingle', $isSingle);
        if ($shop) {
            $qb->andWhere('p.shop = :shop')->setParameter('shop', $shop);
        }
        $qb->setMaxResults($_limit);

        return $qb->getQuery()->getResult();
    }
}
