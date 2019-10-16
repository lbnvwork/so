<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 18.09.18
 * Time: 9:07
 */

namespace Cms\Service;

use App\Helper\UrlHelper;
use App\Service\DateTime;
use Doctrine\ORM\EntityManager;

/**
 * Class WidgetsClientReferral
 *
 * @package Cms\Service
 */
class WidgetsClientReferral
{
    private $entityManager;

    private $user;

    private $companies;

    private $urlHelper;

    /**
     * WidgetsClientReferral constructor.
     *
     * @param EntityManager $entityManager
     * @param UrlHelper $urlHelper
     * @param $user
     * @param $companies
     */
    public function __construct(EntityManager $entityManager, UrlHelper $urlHelper, $user, $companies)
    {
        $this->entityManager = $entityManager;
        $this->user = $user;
        $this->companies = $companies;
        $this->urlHelper = $urlHelper;
    }

    /*
        public function small($params = null)
        {
            $data  = array();
            $data['items'] =  array();
            $data['label'] = 'Баланс';
            $data['icon'] = 'fa-rouble';
            $total = 0;


            foreach($this->companies as $company){

                $item = array();
                $item['value'] =  (int)$company->getBalance();
                $item['label'] = $company->getTitle();
                if($item['value'] > 0) {
                    $item['class'] = 'green';
                } else {
                    $item['class'] = 'red';
                    $total = $total + $item['value'];
                }
                $data['items'][] = $item;
            }

            $data['total'] =  $total;

            if(!empty($data['items']))  {
                return $data;
            } else {
                return false;
            }

        } */

    /**
     * @param array|null $params
     *
     * @return array|null
     * @throws \Exception
     */
    public function table(?array $params): ?array
    {
        $month = $params['month']; // 4

        $data = [];
        $data['label'] = 'Реферальная статистика';
        $data['items'] = [];


        $curDate0 = new DateTime();
        $prewDate = new DateTime($curDate0->format('Y-m'));
        $curDate = new DateTime($curDate0->format('Y-m'));
        $prewDate->sub(new \DateInterval("P".$month."M"));

        $results =
            $this->entityManager->createQueryBuilder()
                ->select('r')
                ->from(\Office\Entity\ReferralPayment::class, 'r')
                ->where('r.datetime >= :data')
                ->andWhere('r.user = :user')
                ->setParameters(
                    [
                        'data' => $prewDate,
                        'user' => $this->user
                    ]
                )->orderBy('r.datetime', 'DESC')
                ->getQuery()
                ->getResult();

        if ($results) {
            $row = true;
            $count = count($results);
            $p = 0;
            $n = 0;

            foreach ($results as $result) {
                if ($result->getDatetime() < $curDate) {
                    if ($p > 0 || $n > 0) {
                        $data['items'][] = [
                            'p'     => (($p > 0) ? $p : ''),
                            'n'     => (($n > 0) ? $n : ''),
                            'month' => $curDate->format('M'),
                        ];
                    }
                    $curDate->sub(new \DateInterval("P1M"));
                    $p = 0;
                    $n = 0;
                }
                if ($result->getDatetime() < $curDate) {
                    continue;
                }

                if ($result->getPay() == 1) {
                    $p = $p + $result->getSum();
                } else {
                    $n = $n + $result->getSum();
                }

                $count--;
                if ($count === 0 && ($p > 0 || $n > 0)) {
                    $data['items'][] = [
                        'p'     => (($p > 0) ? $p : ''),
                        'n'     => (($n > 0) ? $n : ''),
                        'month' => $result->getDatetime()->format('M'),
                    ];
                }
            }

            $data['th'] = [
                'p'     => 'Выплачено',
                'n'     => 'Ожидает оплаты',
                'month' => 'Месяц',
            ];

            return $data;
        }

        return null;
    }
}
