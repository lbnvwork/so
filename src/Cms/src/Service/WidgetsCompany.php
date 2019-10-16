<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 18.09.18
 * Time: 9:08
 */

namespace Cms\Service;

use App\Service\DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Office\Entity\Company;

/**
 * Class WidgetsCompanyService
 *
 * @package Cms\Service
 */
class WidgetsCompany
{
    private $entityManager;

    private $urlHelper;

    /**
     * WidgetsCompany constructor.
     *
     * @param EntityManager $entityManager
     * @param $urlHelper
     */
    public function __construct(EntityManager $entityManager, $urlHelper)
    {
        $this->entityManager = $entityManager;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param array|null $params
     *
     * @return array|null
     * @throws NonUniqueResultException
     * @throws \Exception
     */
    public function small(?array $params): ?array
    {
        $day = $params['day']; //30;

        $data = $item = [];
        $data['items'] = [];
        $data['label'] = 'Компании';
        $data['icon'] = 'fa-building';

        $data['total'] = $this->entityManager->createQueryBuilder()
            ->select('COUNT(u)')
            ->from(Company::class, 'u')
            ->getQuery()
            ->getSingleScalarResult();


        $curDate = new DateTime();
        $curDate->sub(new \DateInterval('P'.$day.'D'));

        $item = [];
        $item['value'] = $this->entityManager->createQueryBuilder()
            ->select('COUNT(u)')
            ->from(Company::class, 'u')
            ->where('u.date >= :dateCreate')
            ->setParameter('dateCreate', $curDate)
            ->getQuery()
            ->getSingleScalarResult();
        if ($item['value']) {
            $item['label'] = 'Новых (за последние '.$day.' дней)';
            $item['class'] = 'green';
            $data['items'][] = $item;
        }

        $item = [];
        $item['value'] =
            $this->entityManager->createQueryBuilder()
                ->select('COUNT(u)')
                ->from(Company::class, 'u')
                ->where('u.isDeleted = 1')
                ->getQuery()
                ->getSingleScalarResult();
        if ($item['value']) {
            $item['label'] = 'Удалённых';
            $item['class'] = 'red';
            $data['items'][] = $item;
        }


        return $data;
    }
}
