<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 18.09.18
 * Time: 9:09
 */

namespace Cms\Service;

use App\Helper\UrlHelper;
use App\Service\DateTime;
use DateInterval;
use Doctrine\ORM\EntityManager;
use Exception;
use Office\Entity\Invoice;
use Office\Entity\ReferralPayment;

/**
 * Class WidgetsReferral
 *
 * @package Cms\Service
 */
class WidgetsReferral
{
    private $entityManager;

    private $urlHelper;

    /**
     * WidgetsReferral constructor.
     *
     * @param EntityManager $entityManager
     * @param UrlHelper $urlHelper
     */
    public function __construct(EntityManager $entityManager, UrlHelper $urlHelper)
    {
        $this->entityManager = $entityManager;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param $params
     *
     * @return array
     * @throws Exception
     */
    public function small(array $params): array
    {
        $day = $params['day']; //30;

        $data = $item = [];
        $data['items'] = [];
        $data['label'] = 'Привлечено рефералами';
        $data['icon'] = 'fa-dollar';

        $data['total'] = $this->entityManager->createQueryBuilder()
            ->select('COUNT(k)')
            ->from(ReferralPayment::class, 'k')
            ->getQuery()
            ->getSingleScalarResult();

        $curDate = new DateTime();
        $curDate->sub(new DateInterval('P'.$day.'D'));

        $item = [];
        $item['label'] = 'За последние '.$day.' дней';
        $item['value'] = $this->entityManager->createQueryBuilder()
            ->select('COUNT(r)')
            ->from(ReferralPayment::class, 'r')
            ->where('r.datetime >= :dateCreate')->setParameter('dateCreate', $curDate)
            ->getQuery()
            ->getSingleScalarResult();
        if ($item['value']) {
            $item['class'] = 'green';
            $data['items'][] = $item;
        }


        return $data;
    }

    /**
     * @param $params
     *
     * @return array|null
     * @throws Exception
     */
    public function table(array $params): ?array
    {
        $limit = $params['limit']; // 8;

        $data = [];
        $data['label'] = 'Реферальная статистика';
        $data['items'] = [];

        $curDate0 = new DateTime();
        $curDate = new DateTime($curDate0->format('Y-m'));
        $prewDate = new DateTime($curDate0->format('Y-m'));
        $start = $endP = $curDate->format('Y-m-d H:i:s');
        $monthCur = $curDate->format('M');
        $curDate->add(new DateInterval("P1M"));
        $end = $curDate->format('Y-m-d H:i:s');

        $prewDate->sub(new DateInterval("P1M"));
        $monthPrew = $prewDate->format('M');
        $startP = $prewDate->format('Y-m-d H:i:s');

        $results = $this->entityManager->createQueryBuilder()->select(
            [
                '(SUM(i.sum) - (COUNT(i.id) * 500) ) sd',
                '(COUNT(i.id) * 500) sk',
                'u.lastName',
                'u.firstName',
                'u.middleName'
            ]
        )->from(Invoice::class, 'i')
            ->innerJoin('i.company', 'c')
            ->innerJoin('c.referral', 'r')
            ->innerJoin('r.user', 'u')
            ->where('r.pay = 0 AND r.datetime >= :start AND r.datetime < :end AND i.date >= :start AND i.date < :end AND i.status = 1')
            ->setParameters(
                [
                    'start' => $startP,
                    'end'   => $endP
                ]
            )->groupBy('u.id')
            ->orderBy('sd', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        if ($results) {
            foreach ($results as $result) {
                $data['items'][] = [
                    'fio'   => $result['lastName'].' '.$result['firstName'].' '.$result['middleName'],
                    'sd'    => $result['sd'],
                    'sum'   => $result['sk'],
                    'month' => $monthPrew,
                ];
            }
        }

        $results = $this->entityManager->createQueryBuilder()->select(
            [
                '(SUM(i.sum) - (COUNT(i.id) * 500) ) sd',
                '(COUNT(i.id) * 500) sk',
                'u.lastName',
                'u.firstName',
                'u.middleName'
            ]
        )->from(Invoice::class, 'i')
            ->innerJoin('i.company', 'c')
            ->innerJoin('c.referral', 'r')
            ->innerJoin('r.user', 'u')
            ->where('r.pay = 0 AND r.datetime >= :start AND r.datetime < :end AND i.date >= :start AND i.date < :end AND i.status = 1')
            ->setParameters(
                [
                    'start' => $start,
                    'end'   => $end
                ]
            )->groupBy('u.id')
            ->orderBy('sd', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();


        if ($results) {
            foreach ($results as $result) {
                $data['items'][] = [
                    'fio'   => $result['lastName'].' '.$result['firstName'].' '.$result['middleName'],
                    'sd'    => $result['sd'],
                    'sum'   => $result['sk'],
                    'month' => $monthCur,
                ];
            }
        }

        $data['th'] = [
            'fio'   => 'ФИО дилера',
            'sd'    => 'Оплачено рефералами',
            'sk'    => 'Долг перед дилером',
            'month' => 'Месяц',
        ];


        if (!empty($data['items'])) {
            return $data;
        }

        return null;
    }
}
