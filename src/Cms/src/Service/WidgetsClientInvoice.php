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
use Office\Entity\Company;
use Office\Entity\Invoice;
use Office\Entity\Kkt;
use Office\Entity\Shop;

/**
 * Class WidgetsClientInvoice
 *
 * @package Cms\Service
 */
class WidgetsClientInvoice
{
    private $entityManager;

    private $user;

    /**
     * @var Company[]
     */
    private $companies;

    private $urlHelper;

    /**
     * WidgetsClientInvoice constructor.
     *
     * @param EntityManager $entityManager
     * @param $urlHelper
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

    /**
     * @param array|null $params
     *
     * @return array|null
     * @throws \Exception
     */
    public function small(?array $params = null): ?array
    {
        $data = [];
        $data['items'] = [];
        $data['label'] = 'Баланс';
        $data['icon'] = 'fa-rouble';
        $total = 0;

        $kktD = [];
        $curDate = new DateTime();
        $months = 0;

        //$service = $this->entityManager->getRepository(\App\Entity\Service::class)->find(2);
        //$price = $service->getPrice();

        foreach ($this->companies as $company) {
            if ($company->getIsDeleted() == 1) {
                continue;
            }
            $kkts = $this->entityManager->createQueryBuilder()
                ->select('k.dateExpired')
                ->from(Shop::class, 's')
                ->innerJoin('s.kkt', 'k')
                ->where('k.isEnabled = 1 AND s.company = :company AND k.dateExpired IS NOT NULL')
                ->setParameter('company', $company)
                ->getQuery()
                ->getArrayResult();

            foreach ($kkts as $kkt) {
                $dateExpired = $kkt['dateExpired'];
                if ($dateExpired > $curDate) {
                    $interval = $dateExpired->diff($curDate);
                    $months = $months + (int)$interval->format('%m');
                }
            }


            $item = [];
            $item['value'] = (int)$company->getBalance();
            $item['label'] = $company->getTitle();
            if ($item['value'] > 0) {
                $item['class'] = 'green';
                $total = $total + $item['value'];
            } else {
                $item['class'] = 'red';
            }
            $data['items'][] = $item;
        }


        $data['total'] = $total;

        return !empty($data['items']) ? $data : null;
    }

    /**
     * @param array $params
     *
     * @return array|null
     */
    public function table(array $params = ['limit' => 8]): ?array
    {

        $data = [];
        $data['label'] = 'Не оплаченные счета';
        $data['items'] = [];
        $limit = $params['limit'];

        $results = $this->entityManager->createQueryBuilder()->select(
            [
                'i.sum',
                'i.id',
                /*'i.status',*/
                'i.date',
                'c.title'
            ]
        )->from(Invoice::class, 'i')
            ->innerJoin('i.company', 'c')
            ->where('c IN (:company) AND i.status = 0')
            ->setParameter('company', $this->companies)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();


        if ($results) {
            foreach ($results as $result) {
                $data['items'][] = [
                    'date'     => $result['date']->format('d.m.Y'),
                    'company'  => $result['title'],
                    'sum'      => $result['sum'],
                    //'status' => ( ($result['status'] === 0)
                    // ? '<span class="label label-warning">Не оплачен</span>'
                    // : '<span class="label label-success">Оплачен</span>' ),
                    'download' => '<a  href="'.$this->urlHelper->generate('office.invoice', ['id' => $result['id']]).'">PDF</a>'
                ];
            }

            $data['th'] = [
                'date'     => 'Дата',
                'company'  => 'Компания',
                'sum'      => 'Сумма',
                //'status' => 'Статус' ,
                'download' => 'Скачать'
            ];

            return $data;
        }

        return null;
    }

    /**
     * @param null $params
     *
     * @return array
     */
    public function tariffInfo(? array $params = null): array
    {
        $data = [
            'items' => [],
            'label' => 'Информация о кассах'
        ];
        /** @var Company $company */
        foreach ($this->companies as $company) {
            if ($company->getIsDeleted() == 1) {
                continue;
            }
            $companyTitleFirst = $fc = mb_strtoupper(mb_substr($company->getTitle(), 0, 1));
            $companyTitle = $companyTitleFirst.mb_substr(mb_strtolower($company->getTitle()), 1);
            $data['items'][$company->getId()]['title'] = $companyTitle;
            $data['items'][$company->getId()]['shops'] = [];
            $shops = $company->getShop();
            /** @var Shop $shop */
            foreach ($shops as $shop) {
                $data['items'][$company->getId()]['shops'][$shop->getId()]['title'] = $shop->getTitle();
                $data['items'][$company->getId()]['shops'][$shop->getId()]['kkts'] = [];
                $data['items'][$company->getId()]['shops'][$shop->getId()]['th'] = [
                    'kkt'          => 'Номер кассы',
                    'tariff'       => 'Тариф',
                    'dateExpiried' => 'Срок действия',
                    //'currentCost'  => 'Текущие расходы',
                    'rentCost'     => 'Размер аренды'
                ];
                $kkts = $shop->getKkt();
                /** @var Kkt $kkt */
                foreach ($kkts as $kkt) {
                    if (!empty($kkt->getDateExpired()) && !empty($kkt->getIsEnabled()) && !empty($kkt->getTariff())) {
                        $turnover = $this->entityManager->getRepository(Kkt::class)->getMonthTurnoverKkt($kkt);
                        $turnoverPercent = $this->entityManager->getRepository(Kkt::class)->getMonthTurnoverPercentKkt($kkt);
                        //$monthBeforeDateExpiried = clone $kkt->getDateExpired();

                        /*if (new \DateTime() > $monthBeforeDateExpiried->sub(new \DateInterval('P1M'))) {
                            $currentCost = $this->entityManager->getRepository(Kkt::class)->getKktPrice($kkt);
                        } else {
                            $currentCost = 0;
                        }*/
                        $data['items'][$company->getId()]['shops'][$shop->getId()]['kkts'][$kkt->getId()]['serialNumber'] = $kkt->getSerialNumber();
                        $data['items'][$company->getId()]['shops'][$shop->getId()]['kkts'][$kkt->getId()]['tariffTitle'] = $kkt->getTariff()->getTitle();
                        $data['items'][$company->getId()]['shops'][$shop->getId()]['kkts'][$kkt->getId()]['dateExpiried'] = $kkt->getDateExpired()->format('d.m.Y');
                        //$data['items'][$company->getId()]['shops'][$shop->getId()]['kkts'][$kkt->getId()]['currentCost'] = $currentCost;
                        $data['items'][$company->getId()]['shops'][$shop->getId()]['kkts'][$kkt->getId()]['rentCost'] = $kkt->getTariff()->getRentCost();
                        if (!empty($kkt->getTariff()->getMonthCount())) {
                            $data['items'][$company->getId()]['shops'][$shop->getId()]['th']['monthCount'] = 'Мин. к-во месяцев';
                            $data['items'][$company->getId()]['shops'][$shop->getId()]['kkts'][$kkt->getId()]['monthCount'] = $kkt->getTariff()->getMonthCount();
                        }
                        if (!empty($kkt->getTariff()->getMaxTurnover())) {
                            $data['items'][$company->getId()]['shops'][$shop->getId()]['th']['maxTurnover'] = 'Лимит оборота';
                            $data['items'][$company->getId()]['shops'][$shop->getId()]['kkts'][$kkt->getId()]['maxTurnover'] = $kkt->getTariff()->getMaxTurnover();
                        }
                        if (!empty($kkt->getTariff()->getMaxTurnover()) || !empty($kkt->getTariff()->getTurnoverPercent())) {
                            $data['items'][$company->getId()]['shops'][$shop->getId()]['th']['turnover'] = 'Текущий оборот';
                            $data['items'][$company->getId()]['shops'][$shop->getId()]['kkts'][$kkt->getId()]['turnover'] = $turnover;
                        }
                        if (!empty($kkt->getTariff()->getTurnoverPercent())) {
                            $data['items'][$company->getId()]['shops'][$shop->getId()]['th']['turnoverPercent'] = 'Процент оборота';
                            $data['items'][$company->getId()]['shops'][$shop->getId()]['kkts'][$kkt->getId()]['turnoverPercent'] = $turnoverPercent;
                        }
                    }
                }
            }
        }

        return $data;
    }
}
