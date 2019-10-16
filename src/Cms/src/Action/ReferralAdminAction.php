<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 18.09.18
 * Time: 9:03
 */

namespace Cms\Action;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Office\Entity\ReferralPayment;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class ReferralAdminAction
 *
 * @package Cms\Action
 */
class ReferralAdminAction extends AbstractAction
{
    public const COUNT_ITEMS = 20;

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        $params = [
            'r.id',
            'r.sum',
            'r.pay',
            'u.lastName',
            'r.datetime',
            'c.title',
            'ud.lastName',
        ];

        $field = isset($queryParams['sort']) && in_array($queryParams['sort'], $params) ? $queryParams['sort'] : 'r.id';

        $orderCheck = isset($queryParams['order'])
        && in_array(
            $queryParams['order'],
            [
            'DESC',
            'ASC',
            ]
        ) ? $queryParams['order'] : 'ASC';

        $orderType = $orderCheck === 'ASC' ? 'DESC' : 'ASC';
        $chevron = $orderType === 'ASC' ? 'down' : 'up';

        $filter = $queryParams['filter'] ?? false;
        $filter2 = [
            'name'  => '',
            'diler' => '',
            'pay'   => 100,
        ];


        if ($request->getMethod() === 'POST') {
            $paramsPost = $request->getParsedBody();
            if (isset($paramsPost['id']) && is_numeric($paramsPost['id'])) {
                $referral = $this->entityManager->getRepository(ReferralPayment::class)->findOneBy(['id' => (int)$paramsPost['id']]);
                if ($referral) {
                    $referral->setPay(1);
                    $this->entityManager->persist($referral);
                    $this->entityManager->flush();
                }
            }
        }


        $page = isset($queryParams['page']) && $queryParams['page'] > 0 ? $queryParams['page'] : 1;

        $qb = $this->entityManager->createQueryBuilder()
            ->select('u, c, r')
            ->from(ReferralPayment::class, 'r')
            ->innerJoin('r.company', 'c')
            ->innerJoin('r.fromUser', 'u')
            ->innerJoin('r.user', 'ud');

        $searchArr = [];

        if ($filter && is_array($filter)) {
            if (isset($filter['name']) && !empty($filter['name'])) {
                $search = preg_replace('/[^a-zA-Zа-яА-Я0-9\s.,]/ui', ' ', (string)$filter['name']);
                $search = preg_replace('|\s+|', ' ', $search);
                $search = $filter2['name'] = trim($search);
                $search2 = '%'.str_replace(' ', '%', $search).'%';
                $searchArr[] = ' (u.lastName LIKE :name OR c.title LIKE :name) ';
                $qb->setParameter('name', $search2);
            }

            if (isset($filter['diler']) && !empty($filter['diler'])) {
                $search = preg_replace('/[^a-zA-Zа-яА-Я0-9\s.,]/ui', ' ', (string)$filter['diler']);
                $search = preg_replace('|\s+|', ' ', $search);
                $search = $filter2['diler'] = trim($search);
                $search2 = '%'.str_replace(' ', '%', $search).'%';
                $searchArr[] = ' ud.lastName LIKE :diler ';
                $qb->setParameter('diler', $search2);
            }

            if (isset($filter['pay'])
                && in_array(
                    $filter['pay'],
                    [
                    '0',
                    '1',
                    ]
                )) {
                $filter2['pay'] = $filter['pay'];
                $searchArr[] = 'r.pay = :pay';
                $qb->setParameter('pay', $filter['pay']);
            }
        }

        if (!empty($searchArr)) {
            $qb->where(implode(' AND ', $searchArr));
        }


        $totalRows = (new Paginator($qb->getQuery()))->count();

        $paginator = [
            'countItems'   => $totalRows,
            'query'        => $queryParams,
            'currentPage'  => $page,
            'itemsPerPage' => self::COUNT_ITEMS,
            'url'          => $this->urlHelper->generate('admin.referral'),
        ];


        $referrals = $qb->setMaxResults(self::COUNT_ITEMS)
            ->orderBy($field, $orderCheck)
            ->setFirstResult(self::COUNT_ITEMS * ($page - 1))
            ->getQuery()->getResult();

        return new HtmlResponse(
            $this->template->render(
                'admin::referrals-pay',
                [
                    'referrals' => $referrals,
                    'sortType'  => $field,
                    'order'     => $orderType,
                    'chevron'   => $chevron,
                    'paginator' => $paginator,
                    'filter'    => $filter2,
                ]
            )
        );
    }
}
