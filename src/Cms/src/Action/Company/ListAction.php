<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 09.04.18
 * Time: 12:39
 */

namespace Cms\Action\Company;

use Cms\Action\AbstractAction;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\ResultSetMapping;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class ListAction
 *
 * @package Cms\Action\Company
 */
class ListAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::company/list';

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     * @throws NonUniqueResultException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $countItems = 20;

        $form = [];

        $form['name_type'] = [
            'c.title'              => 'Наименованию компании',
            'u.last_name'          => 'Фамилии пользователя',
            'u.first_name'         => 'Имени пользователя',
            'c.id'                 => 'id компании',
            'c.inn'                => 'ИНН',
            'c.kpp'                => 'КПП',
            'c.ogrn'               => 'ОГРН',
            'c.address'            => 'Адресу',
            'u.phone'              => 'Телефону пользователя',
            'c.company_phone'      => 'Телефону компании',
            'u.email'              => 'email пользователя',
            'c.company_email'      => 'email компании',
            'c.director_last_name' => 'По фамилии директора',
        ];

        $form['c.is_deleted'] = [
            1 => 'Удаленная',
            0 => 'Активная',
        ];

        $form['kkt'] = [
            1 => 'Без касс',
            2 => 'Любая касса',
            3 => 'Активные',
            4 => 'Неактивные',
            5 => 'Только активные',
            6 => 'Только неактивные',
        ];

        $form['c.nalog_system'] = [
            0 => 'общая',
            1 => 'УСН Доход',
            2 => 'УСН Доход минус расход',
            3 => 'ЕНВД',
            4 => 'ЕСХН',
            5 => 'ПСН',
        ];

        $form['c.org_type'] = [
            0 => 'Юр. лицо',
            1 => 'ИП',
        ];


        $filter2 = [
            'name'           => '',
            'name_type'      => 'c.title',
            'c.is_deleted'   => 0,
            'kkt'            => 100,
            'c.nalog_system' => 100,
            'c.org_type'     => 100,
        ];

        $queryParams = $request->getQueryParams();
        $params = [
            'c.id',
            'c.title',
            'u.last_name',
            'c.date',
            'act',
        ];
        $field = isset($queryParams['sort']) && in_array($queryParams['sort'], $params) ? $queryParams['sort'] : 'c.id';
        $orderCheck = isset($queryParams['order'])
        && in_array(
            $queryParams['order'],
            [
                'DESC',
                'ASC',
            ]
        ) ? $queryParams['order'] : 'ASC';
        $page = isset($queryParams['page']) && $queryParams['page'] > 0 ? $queryParams['page'] : 1;


        $orderType = $orderCheck === 'ASC' ? 'DESC' : 'ASC';
        $chevron = $orderType === 'ASC' ? 'down' : 'up';


        $setParameter = [];
        $searchArr = [];
        $having = false;

        if (isset($queryParams['filter']) && is_array($queryParams['filter'])) {
            $filter = $queryParams['filter'];
            if (isset($filter['name']) && !empty($filter['name']) && isset($filter['name_type']) && isset($form['name_type'][$filter['name_type']])) {
                $search = preg_replace('/[^a-zA-Zа-яА-Я0-9\s.,\@]/ui', ' ', (string)$filter['name']);
                $search = preg_replace('|\s+|', ' ', $search);
                $search = $filter2['name'] = trim($search);
                $search2 = '%'.str_replace(' ', '%', $search).'%';
                $searchArr[] = $filter['name_type'].' LIKE :name ';
                $setParameter['name'] = $search2;
                $filter2['name_type'] = $filter['name_type'];
            }

            if (isset($filter['kkt']) && !empty($filter['kkt']) && isset($form['kkt'][$filter['kkt']])) {
                switch ($filter['kkt']) {
                    case 1:
                        $having = ' act = 0 AND nact = 0 ';
                        break;
                    case 2:
                        $having = ' act > 0 OR nact > 0 ';
                        break;
                    case 3:
                        $having = ' act > 0 ';
                        break;
                    case 4:
                        $having = ' nact > 0 ';
                        break;
                    case 5:
                        $having = ' act > 0 AND nact = 0 ';
                        break;
                    case 6:
                        $having = ' act = 0 AND nact > 0 ';
                        break;
                }
                $filter2['kkt'] = $filter['kkt'];
            }

            $ch = 0;

            foreach ($filter as $name => $value) {
                if ($name == 'kkt' || $name == 'name_type' || !isset($form[$name]) || !isset($form[$name][$value])) {
                    continue;
                }

                $ch++;
                $searchArr[] = $name.' = :val'.$ch;
                $setParameter['val'.$ch] = $value;
                $filter2[$name] = $value;
            }
        }

        $where = '';
        if (!empty($searchArr)) {
            $where = ' WHERE '.implode(' AND ', $searchArr).' ';
        }

        if ($having) {
            $where .= ' HAVING '.$having;
        }

        if ($field == 'act') {
            $sort = 'act '.$orderCheck.', nact '.$orderCheck;
        } else {
            $sort = $field.' '.$orderCheck;
        }


        $rsm = new ResultSetMapping;
        $rsm->addEntityResult('Office\Entity\Company', 'c');
        $rsm->addJoinedEntityResult('Office\Entity\Kkt', 'k', 's', 'kkt');
        $rsm->addJoinedEntityResult('Office\Entity\Shop', 's', 'c', 'shop');
        $rsm->addJoinedEntityResult('Office\Entity\Kkt', 'kk', 's', 'kkt');
        $rsm->addJoinedEntityResult('Office\Entity\Shop', 'ss', 'c', 'shop');
        $rsm->addJoinedEntityResult('Auth\Entity\User', 'u', 'c', 'user');
        $rsm->addFieldResult('c', 'id', 'id');
        $rsm->addFieldResult('c', 'title', 'title');
        $rsm->addFieldResult('c', 'date', 'date');
        $rsm->addFieldResult('c', 'title', 'title');
        $rsm->addMetaResult('c', 'act', 'act');
        $rsm->addMetaResult('c', 'nact', 'nact');
        $rsm->addMetaResult('c', 'last_name', 'last_name');
        $rsm->addMetaResult('c', 'first_name', 'first_name');
        $rsm->addMetaResult('c', 'middle_name', 'middle_name');
        $rsm->addMetaResult('c', 'ip_last_name', 'ip_last_name');
        $rsm->addMetaResult('c', 'ip_first_name', 'ip_first_name');
        $rsm->addMetaResult('c', 'ip_middle_name', 'ip_middle_name');
        $rsm->addMetaResult('c', 'director_last_name', 'director_last_name');
        $rsm->addMetaResult('c', 'director_first_name', 'director_first_name');
        $rsm->addMetaResult('c', 'director_middle_name', 'director_middle_name');
        $rsm->addMetaResult('c', 'userid', 'userid');

        $sql = 'SELECT 
                SQL_CALC_FOUND_ROWS c.id, 
                c.title, 
                c.ip_last_name, 
                c.ip_first_name, 
                c.ip_middle_name, 
                c.director_last_name, 
                c.director_first_name, 
                c.director_middle_name, 
                u.last_name, 
                u.first_name, 
                u.middle_name, 
                c.date, 
                u.id userid,
        (SELECT COUNT(k.id)  FROM kkt k inner join shop s ON s.id = k.shop_id
        WHERE k.shop_id = s.id and k.serial_number IS NOT NULL and k.is_deleted = 0 AND s.company_id = c.id) act,
        (SELECT COUNT(kk.id)  FROM kkt kk inner join shop ss ON ss.id = kk.shop_id
        WHERE kk.shop_id = ss.id and (kk.serial_number IS NULL OR kk.is_deleted = 1) AND ss.company_id = c.id) nact
        FROM company c 
        inner join user_has_company hc ON hc.company_id = c.id
        inner join user u ON u.id = hc.user_id '.$where.
            'ORDER BY '.$sort.' LIMIT '.$countItems * ($page - 1).', '.$countItems;

        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        if (!empty($setParameter)) {
            $query->setParameters($setParameter);
        }


        $companies = $query->getArrayResult();
        $rsm = new ResultSetMapping;
        $rsm->addScalarResult('cr', 'cr');
        $query = $this->entityManager->createNativeQuery("SELECT FOUND_ROWS() cr", $rsm);
        $totalRows = $query->getSingleScalarResult();

        $paginator = [
            'countItems'   => $totalRows,
            'query'        => $queryParams,
            'currentPage'  => $page,
            'itemsPerPage' => $countItems,
            'url'          => $this->urlHelper->generate('admin.company.list'),
        ];


        return new HtmlResponse(
            $this->template->render(
                self::TEMPLATE_NAME,
                [
                    'sortType'  => $field,
                    'order'     => $orderType,
                    'chevron'   => $chevron,
                    'companies' => $companies,
                    'filter'    => $filter2,
                    'form'      => $form,
                    'paginator' => $paginator,
                ]
            )
        );
    }
}
