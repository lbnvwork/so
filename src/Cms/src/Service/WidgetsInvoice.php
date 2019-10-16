<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 18.09.18
 * Time: 9:08
 */
declare(strict_types=1);

namespace Cms\Service;

use App\Service\DateTime;
use Doctrine\ORM\EntityManager;
use Office\Entity\Invoice;

/**
 * Class WidgetsInvoice
 *
 * @package Cms\Service
 */
class WidgetsInvoice
{
    private $entityManager;

    /**
     * WidgetsInvoice constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param array|null $param
     *
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function small(?array $param = []): array
    {
        $data = [
            'items' => [],
            'label' => 'Счета',
            'icon'  => 'fa-file-text-o'
        ];

        $item = [];
        $total = 0;

        $results = $this->entityManager->createQueryBuilder()->select(
            [
                'COUNT(k) c',
                'SUM(k.sum) s'
            ]
        )->from(Invoice::class, 'k')
            ->where('k.status = 1')
            ->groupBy('k.status')
            ->getQuery()->getOneOrNullResult();

        if ($results) {
            $item['value'] = (int)$results['c'];
            $item['class'] = 'green';
            $item['label'] = 'Оплаченые ('.number_format((float)$results['s'], 0, ',', ' ').'р.)';
            $data['items'][] = $item;
            $total += (int)$results['c'];
        }

        $results = $this->entityManager->createQueryBuilder()->select(
            [
                'COUNT(k) c',
                'SUM(k.sum) s'
            ]
        )->from(Invoice::class, 'k')
            ->where('k.status = 0')
            ->groupBy('k.status')
            ->getQuery()->getOneOrNullResult();
        if ($results) {
            $item['value'] = (int)$results['c'];
            $item['class'] = 'red';
            $item['label'] = 'Без оплаты ('.number_format((float)$results['s'], 0, ',', ' ').'р.)';
            $data['items'][] = $item;
            $total += (int)$results['c'];
        }

        $data['total'] = $total;


        return $data;
    }

    /**
     * @param array $params
     *
     * @return array|null
     */
    public function table(array $params = ['limit' => 8]): ?array
    {
        $data = [];
        $data['label'] = 'Последние изменения по счетам';
        $data['items'] = [];

        /** @var Invoice[] $invoices */
        $invoices = $this->entityManager->getRepository(Invoice::class)->findBy([], ['dateUpdate' => 'DESC'], $params['limit']);

        if ($invoices) {
            foreach ($invoices as $invoice) {
                $company = $invoice->getCompany();
                $companyTitle = $company->getDefaultTitle();
                $data['items'][] = [
                    'date'    => $invoice->getDate()->format('d.m.Y'),
                    'company' => $companyTitle,
                    'sum'     => $invoice->getSum(),
                    'status'  =>
                        $invoice->getStatus() === 0
                            ? '<span class="label label-warning">Не оплачен</span>'
                            : '<span class="label label-success">Оплачен</span>',
                ];
            }

            $data['th'] = [
                'date'    => 'Дата',
                'company' => 'Компания',
                'sum'     => 'Сумма',
                'status'  => 'Статус',
            ];
        }

        if (!empty($data['items'])) {
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
            'label' => 'Статистика по оплатам',
            'th'    => [],
            'items' => [],
            'width' => 12
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

            $count = $this->entityManager->getRepository(Invoice::class)
                ->createQueryBuilder('i')
                ->select('SUM(i.sum)')
                ->where('i.date BETWEEN :dateStart AND :dateEnd')
                ->setParameter('dateStart', $date->format('Y-m-1 00:00:00'))
                ->setParameter('dateEnd', $date->format('Y-m-31 23:59:59'))
                ->getQuery()->getSingleScalarResult();
            $countAccept = $this->entityManager->getRepository(Invoice::class)
                ->createQueryBuilder('i')
                ->select('SUM(i.sum)')
                ->where('i.dateAccept BETWEEN :dateStart AND :dateEnd')
                ->setParameter('dateStart', $date->format('Y-m-1 00:00:00'))
                ->setParameter('dateEnd', $date->format('Y-m-31 23:59:59'))
                ->getQuery()->getSingleScalarResult();
            $countInvoice = $this->entityManager->getRepository(Invoice::class)
                ->createQueryBuilder('i')
                ->select('COUNT(i)')
                ->where('i.dateAccept BETWEEN :dateStart AND :dateEnd')
                ->setParameter('dateStart', $date->format('Y-m-1 00:00:00'))
                ->setParameter('dateEnd', $date->format('Y-m-31 23:59:59'))
                ->getQuery()->getSingleScalarResult();


            $data['items'][0][] = $count;
            $data['items'][1][] = $countAccept;
            $data['items'][2][] = $countInvoice;
            $data['items'][3][] = $countInvoice ? number_format($countAccept / $countInvoice, 2, ',', ' ') : 0;

            $date->addMonths(1);
        } while ($date <= $currentDate);

        $data['th'][] = 'Итого';
        $data['items'][0][] = array_sum($data['items'][0]);
        $sumInvoice = array_sum($data['items'][1]);
        $data['items'][1][] = $sumInvoice;
        $countInvoices = array_sum($data['items'][2]);
        $data['items'][2][] = $countInvoices;
        $data['items'][3][] = $countInvoices ? number_format($sumInvoice / $countInvoices, 2, ',', ' ') : 0;

        foreach ($data['items'][0] as $k => $v) {
            $data['items'][0][$k] = number_format((float)$v, 0, ',', ' ');
        }
        foreach ($data['items'][1] as $k => $v) {
            $data['items'][1][$k] = number_format((float)$v, 0, ',', ' ');
        }

        array_unshift($data['th'], '');
        array_unshift($data['items'][0], 'Всего');
        array_unshift($data['items'][1], 'Оплачено');
        array_unshift($data['items'][2], 'Кол-во');
        array_unshift($data['items'][3], 'Средний чек');

        return $data;
    }
}
