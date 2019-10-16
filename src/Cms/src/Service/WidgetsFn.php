<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 18.09.18
 * Time: 9:08
 */

namespace Cms\Service;

use Doctrine\ORM\EntityManager;
use Office\Entity\Fn;

/**
 * Class WidgetsFn
 *
 * @package Cms\Service
 */
class WidgetsFn
{
    private $entityManager;

    private $urlHelper;

    /**
     * WidgetsFn constructor.
     *
     * @param $entityManager
     * @param $urlHelper
     */
    public function __construct(EntityManager $entityManager, $urlHelper)
    {
        $this->entityManager = $entityManager;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param null $params
     *
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function small(?array $params = null): array
    {
        $data = $item = [];
        $data['items'] = [];
        $data['label'] = 'Фискальные накопители';
        $data['icon'] = 'fa-cubes';

        $data['total'] = $this->entityManager->createQueryBuilder()
            ->select('COUNT(k)')
            ->from(Fn::class, 'k')
            ->where('k.isDeleted = 0 OR k.isDeleted IS NULL')
            ->getQuery()
            ->getSingleScalarResult();


        $item = [];
        $item['value'] = $this->entityManager->createQueryBuilder()
            ->select('COUNT(k)')
            ->from(Fn::class, 'k')
            ->andWhere('k.status = 1')
            ->andWhere('k.isFiscalized = 1')
            ->getQuery()->getSingleScalarResult();
        if ($item['value']) {
            $item['label'] = 'Фискализированных и активных';
            $item['class'] = 'green';
            $data['items'][] = $item;
        }

        $item = [];
        $item['value'] = $this->entityManager->createQueryBuilder()
            ->select('COUNT(k)')
            ->from(Fn::class, 'k')
            ->andWhere('k.status = 0')
            ->andWhere('k.isFiscalized IS NULL')
            ->andWhere('k.company IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
        if ($item['value']) {
            $item['label'] = 'Свободных';
            $item['class'] = 'blue';
            $data['items'][] = $item;
        }


        $item = [];
        $item['value'] = $this->entityManager->createQueryBuilder()
            ->select('COUNT(k)')
            ->from(Fn::class, 'k')
            ->where('k.isDeleted = 1')
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
