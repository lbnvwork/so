<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 18.09.18
 * Time: 9:09
 */

namespace Cms\Service;

use App\Service\DateTime;
use Doctrine\ORM\EntityManager;
use Office\Entity\Processing;

/**
 * Class WidgetsProcessing
 *
 * @package Cms\Service
 */
class WidgetsProcessing
{
    private $entityManager;

    /**
     * WidgetsProcessing constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param null $params
     *
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function middle($params = null): array
    {
        $data = [
            'items' => [],
            'total' => 0
        ];

        $day = $params['day']; //7;
        $curDate = new DateTime();
        $curDate->sub(new \DateInterval('P'.$day.'D'));

        foreach (Processing::STATUS_LIST as $status => $statusName) {
            $item = [];
            $item['value'] = $this->entityManager->createQueryBuilder()
                ->select('COUNT(u)')
                ->from(Processing::class, 'u')
                ->where('u.status = :status AND u.datetime >= :dateCreate')
                ->setParameter('status', $status)
                ->setParameter('dateCreate', $curDate)
                ->getQuery()
                ->getSingleScalarResult();
            if ($item['value']) {
                $item['label'] = $statusName;
                $data['items'][] = $item;
                $data['total'] += $item['value'];
            }
        }

        $data['label'] = 'Чеки (за последние '.$day.' дней)'; //Удалить

        return $data;
    }
}
