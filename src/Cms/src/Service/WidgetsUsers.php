<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 18.09.18
 * Time: 9:09
 */
declare(strict_types=1);

namespace Cms\Service;

use Doctrine\ORM\EntityManager;
use App\Service\DateTime;
use Auth\Entity\User;

/**
 * Class WidgetsUsers
 *
 * @package Cms\Service
 */
class WidgetsUsers
{
    private $entityManager;

    /**
     * WidgetsUsers constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param array $params
     *
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function small(array $params = ['day' => 30]): array
    {
        $day = $params['day'];
        $userRole = [
            'admin',
            'manager'
        ];

        $data = $item = [];
        $data['items'] = [];
        $data['label'] = 'Пользователи'; //Удалить
        $data['icon'] = 'fa-user';


        $curDate = new DateTime();
        $curDate->sub(new \DateInterval('P'.$day.'D'));

        $item = [];
        $item['value'] =
            $this->entityManager->createQueryBuilder()
                ->select('COUNT(u)')
                ->from(User::class, 'u')
                ->where('u.dateCreate >= :dateCreate')
                ->setParameter('dateCreate', $curDate)
                ->getQuery()->getSingleScalarResult();
        if ($item['value']) {
            $item['label'] = 'Новых (за последние '.$day.' дней)';
            $item['class'] = 'green';
            $data['items'][] = $item;
        }

        $item = [];
        $item['value'] = $this->entityManager->createQueryBuilder()
            ->select('COUNT(u)')
            ->from(User::class, 'u')
            ->where('u.isConfirmed = 0')
            ->getQuery()->getSingleScalarResult();
        if ($item['value']) {
            $item['class'] = 'red';
            $item['label'] = 'Не подтвержденых';
            $data['items'][] = $item;
        }

        $data['total'] = $this->entityManager->createQueryBuilder()
            ->select('COUNT(u)')
            ->from(User::class, 'u')
            ->innerJoin('u.userRole', 'ur')
            ->where('ur.roleName NOT IN (:roles)')
            ->setParameter('roles', $userRole)
            ->getQuery()->getSingleScalarResult();


        return $data;
    }

    /**
     * @param array $params
     *
     * @return array|null
     */
    public function table(array $params = ['limit' => 8]): ?array
    {
        $limit = $params['limit']; //8;
        $data = [];
        $data['item'] = [];
        $data['label'] = 'Последние зарегистрированные пользователи';

        /** @var User[] $results */
        $results = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->orderBy('u.dateCreate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();

        if ($results) {
            foreach ($results as $result) {
                $data['items'][] = [
                    'fio'         => $result->getFIO(),
                    'email'       => $result->getEmail(),
                    'phone'       => $result->getPhone(),
                    'isConfirmed' => $result->getIsConfirmed() == 1 ? '<span class="label label-success">Активен</span>'
                        : '<span class="label label-warning">Не потвержден</span>',
                    'data'        => $result->getDateCreate()->format('d.m.Y H:i'),
                ];
            }

            $data['th'] = [
                'fio'         => 'ФИО',
                'email'       => 'email',
                'phone'       => 'Телефон',
                'isConfirmed' => 'Статус',
                'data'        => 'Дата регистрации',
            ];

            return $data;
        }


        return null;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function tableStat(): array
    {
        $countMonths = 12;
        $date = (new DateTime())->subMonths($countMonths);

        $data = [
            'label' => 'Статистика по регистрациям',
            'th'    => [],
            'items' => []
        ];
        $months = [
            1 => 'Янв',
            'Фев',
            'Март',
            'Апр',
            'Май',
            'Июнь',
            'Июль',
            'Авг',
            'Сент',
            'Окт',
            'Нояб',
            'Дек'
        ];


        $currentDate = new DateTime();
        do {
            $data['th'][] = $months[(int)$date->format('m')];

            $count = $this->entityManager->getRepository(User::class)
                ->createQueryBuilder('u')
                ->select('COUNT(u)')
                ->where('u.dateCreate BETWEEN :dateStart AND :dateEnd')
                ->setParameter('dateStart', $date->format('Y-m-1 00:00:00'))
                ->setParameter('dateEnd', $date->format('Y-m-31 23:59:59'))
                ->getQuery()->getSingleScalarResult();
            $countActive = $this->entityManager->getRepository(User::class)
                ->createQueryBuilder('u')
                ->select('COUNT(u)')
                ->where('u.dateCreate BETWEEN :dateStart AND :dateEnd AND u.isConfirmed = 1')
                ->setParameter('dateStart', $date->format('Y-m-1 00:00:00'))
                ->setParameter('dateEnd', $date->format('Y-m-31 23:59:59'))
                ->getQuery()->getSingleScalarResult();
            $data['items'][0][] = $count;
            $data['items'][1][] = $countActive;

            $date->addMonths(1);
        } while ($date <= $currentDate);

        $data['th'][] = 'Итого';
        $data['items'][0][] = array_sum($data['items'][0]);
        $data['items'][1][] = array_sum($data['items'][1]);
        foreach ($data['items'][0] as $k => $v) {
            $data['items'][0][$k] = number_format((float)$v, 0, ',', ' ');
        }
        foreach ($data['items'][1] as $k => $v) {
            $data['items'][1][$k] = number_format((float)$v, 0, ',', ' ');
        }

        array_unshift($data['th'], '');
        array_unshift($data['items'][0], 'Всего');
        array_unshift($data['items'][1], 'Подтвержден');

        return $data;
    }
}
