<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 18.09.18
 * Time: 9:08
 */

namespace Cms\Service;

use App\Entity\Service;
use Doctrine\ORM\EntityManager;
use App\Service\DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Office\Entity\Fn;
use Office\Entity\Kkt;

/**
 * Class WidgetsKkt
 *
 * @package Cms\Service
 */
class WidgetsKkt
{
    private $entityManager;

    /**
     * WidgetsKkt constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return array
     * @throws NonUniqueResultException
     */
    public function small(): array
    {
        $data = $item = [];
        $data['items'] = [];
        $data['label'] = 'Статистика ККТ';

        $item['value'] =
            $this->entityManager->createQueryBuilder()
                ->select('COUNT(k)')
                ->from(Kkt::class, 'k')
                ->andWhere('k.isEnabled = 1')
                ->andWhere('k.isFiscalized = 1')
                ->getQuery()
                ->getSingleScalarResult();
        if ($item['value']) {
            $item['label'] = 'Активные';
            $data['items'][] = $item;
        }


        $item = [];
        $item['value'] =
            $this->entityManager->createQueryBuilder()
                ->select('COUNT(k)')
                ->from(Kkt::class, 'k')
                ->andWhere('k.isEnabled = 1')
                ->andWhere('k.isFiscalized = 0')
                ->getQuery()
                ->getSingleScalarResult();
        if ($item['value']) {
            $item['label'] = 'Установленные';
            $data['items'][] = $item;
        }

        $item = [];
        $item['value'] =
            $this->entityManager->createQueryBuilder()
                ->select('COUNT(k)')
                ->from(Kkt::class, 'k')
                ->andWhere('k.isEnabled = 0')
                ->andWhere('k.isFiscalized = 1')
                ->getQuery()->getSingleScalarResult();
        if ($item['value']) {
            $item['label'] = 'Без файлов';
            $data['items'][] = $item;
        }

        $item = [];
        $item['value'] =
            $this->entityManager->createQueryBuilder()
                ->select('COUNT(k)')
                ->from(Kkt::class, 'k')
                ->andWhere('k.isEnabled = 0')
                ->andWhere('k.isFiscalized = 0')
                ->getQuery()
                ->getSingleScalarResult();
        if ($item['value']) {
            $item['label'] = 'Не установлены';
            $data['items'][] = $item;
        }

        $count = 0;
        foreach ($data['items'] as $item) {
            $count += $item['value'];
        }

        $data['total'] = $count;


        $item['value'] =
            $this->entityManager->createQueryBuilder()
                ->select('COUNT(k)')
                ->from(Kkt::class, 'k')
                ->andWhere('k.isDeleted = 1')
                ->getQuery()
                ->getSingleScalarResult();
        if ($item['value']) {
            $item['label'] = 'Удалённые';
            $data['items'][] = $item;
        }


        return $data;
    }

    /**
     * @param $params
     *
     * @return array|null
     * @throws \Exception
     */
    public function table($params): ?array
    {
        $day = $params['day']; // 30;
        $limit = $params['limit']; // 8;
        $limitOperation = $params['limitOperation']; // 210000;
        $month = $params['month']; // 12;


        $month13 = 13; //Срок жизни ФН, не менять пока не появятся ФН с другим срок жизни


        $data = [];
        $data['label'] = 'Пора выставлять счёт';
        $data['items'] = [];

        $data['th'] = [
            'company'     => 'Компания',
            // 'shop' => 'Магазин',
            'dateExpired' => 'Окончание действия',
            'type'        => 'Услуга',
            'sn'          => 'Серийный номер',
        ];

        //Услуга "WEB-Касса" (получаем цену)
        $service = $this->entityManager->getRepository(Service::class)->find(2);

        $curDate = new DateTime();
        $curDate->add(new \DateInterval('P'.$day.'D'));

        $results = $this->entityManager->createQueryBuilder()
            ->select(
                [
                    'k.dateExpired',
                    'k.serialNumber',
                    /*'s.title shop',*/
                    'c.title company'
                ]
            )
            ->from(Kkt::class, 'k')
            ->innerJoin('k.shop', 's')
            ->innerJoin('s.company', 'c')
            ->where('k.dateExpired <= :d AND c.balance < :b')
            ->setParameter('d', $curDate)
            ->setParameter('b', $service->getPrice())
            ->orderBy('k.dateExpired', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();


        if ($results) {
            foreach ($results as $result) {
                $data['items'][] = [
                    'company'     => $result['company'],
                    //'shop' => $result['shop'],
                    'dateExpired' => $result['dateExpired']->format('d.m.Y'),
                    'type'        => 'WEB-Касса',
                    'sn'          => $result['serialNumber'],
                ];
            }
        }


        $curDate = new DateTime();
        $curDate->sub(new \DateInterval('P'.$month.'M'));

        $results = $this->entityManager->createQueryBuilder()
            ->select(
                [
                    'f.documentNumber',
                    'f.company',
                    'f.dateFiscalized',
                    'f.serialNumber'
                ]
            )
            ->from(Fn::class, 'f')
            ->where('(f.dateFiscalized <= :d OR f.documentNumber >= :lo) AND f.status = 1')
            ->setParameters(
                [
                    'd'  => $curDate,
                    'lo' => $limitOperation
                ]
            )
            ->orderBy('f.dateFiscalized', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        if ($results) {
            foreach ($results as $result) {
                if ($result['documentNumber'] > $limitOperation) {
                    $procent = round(($result['documentNumber'] * 100 / 220000), 1);
                    $data['items'][] = [
                        'company'     => $result['company'],
                        'dateExpired' => '',
                        'type'        => 'ФН заполнен '.$procent.'%',
                        'sn'          => $result['serialNumber'],
                    ];
                } else {
                    $data['items'][] = [
                        'company'     => $result['company'],
                        'dateExpired' => $result['dateFiscalized']->add(new \DateInterval('P'.$month13.'M'))->format('d.m.Y'),
                        'type'        => 'ФН',
                        'sn'          => $result['serialNumber'],
                    ];
                }
            }
        }


        if (!empty($data['items'])) {
            return $data;
        }

        return null;
    }
}
