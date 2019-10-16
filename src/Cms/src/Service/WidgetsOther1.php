<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 18.09.18
 * Time: 9:09
 */

namespace Cms\Service;

use App\Service\DateTime;

/**
 * Class WidgetsProcessing
 *
 * @package Cms\Service
 */
class WidgetsOther1
{


    private $entityManager;
    private $urlHelper;

    /**
     * SiteService constructor.
     *
     * @param EntityManager $entityManager
     */


    public function __construct($entityManager, $urlHelper)
    {
        $this->entityManager = $entityManager;
        $this->urlHelper = $urlHelper;
    }


    public function table($params = ['limit' => 5, 'day' => 7])
    {
       //$user = $this->entityManager->getRepository(User::class)->find(55);

        $data =  [];
        $data['items'] = [];


        $day = $params['day'];   //7
        $limit = $params['limit'];   //5

        $curDate = new DateTime();
        $curDate->sub(new \DateInterval('P'.$day.'D'));


        $rsm = new \Doctrine\ORM\Query\ResultSetMapping;
        $rsm->addEntityResult('Office\Entity\Kkt', 'k');
        $rsm->addJoinedEntityResult('Office\Entity\Processing', 'p', 'k', 'processing');
        $rsm->addJoinedEntityResult('Office\Entity\Shop', 's', 'k', 'shop');
        $rsm->addJoinedEntityResult('Office\Entity\Company', 'c', 's', 'company');
       //$rsm->addFieldResult('k', 'id', 'id');
       //$rsm->addMetaResult('s', 'title', 'title', true);
        $rsm->addScalarResult('c', 'c');
        $rsm->addScalarResult('t', 't');
       //$rsm->addFieldResult('u', 'is_fiscalized', 'isFiscalized');
       //$rsm->addMetaResult('p', 'kd', 'kktRawData', true);
        $sql  = 'SELECT c.title t,  
       (SELECT COUNT(p.id) FROM processing p WHERE p.kkt_id = k.id AND p.datetime >= :d ) c  
        FROM kkt k
        INNER JOIN shop s ON k.shop_id = s.id
        INNER JOIN company c ON c.id = s.company_id
        HAVING (c > 0) ORDER BY c DESC  LIMIT :l';
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $query->setParameter('l', $limit);
        $query->setParameter('d', $curDate);
        $results  = $query->getArrayResult();


        if ($results) {
            $data['label'] = 'Активные компании (последние '.$day.' дней)'; //Удалить

            foreach ($results as $result) {
                $data['items'][] = [
                 'shop' => $result['t'],
                 'count' => $result['c'],
                 'count2' => ((!empty($result['c']) && $result['c'] > 0) ? round($result['c'] / $day) : '' ),

                ];
            }

            $data['th'] = [
            'shop' => 'Наименование',
            'count' => 'Кол-во транзакций',
            'count2' => 'Среднее кол-во тр./д.'
            ];



            return $data;
        }



        return false;
    }
}
