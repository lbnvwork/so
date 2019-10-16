<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 24.01.18
 * Time: 11:18
 */

namespace Cms\Action;

use Auth\Entity\User;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Permission\Entity\Role;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class UsersAction
 *
 * @package Cms\Action\Super
 */
class UsersAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::users';
    public const COUNT_ITEMS = 20;

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $sortType = $queryParams['sort'] ?? 'id';

        $order = $queryParams['order'] ?? 'ASC';
        $orderCheck = in_array(
            $order,
            [
                'ASC',
                'DESC',
            ]
        ) ? $order : 'ASC';

        $page = isset($queryParams['page']) && $queryParams['page'] > 0 ? $queryParams['page'] : 1;

        /** @var Role[] $roles */
        $roles = $this->entityManager->getRepository(Role::class)->createQueryBuilder('role', 'role.roleName')->getQuery()->getResult();

        $qb = $this->entityManager->getRepository(User::class)->createQueryBuilder('u');

        if (!empty($queryParams['email'])) {
            $qb->orWhere('u.email LIKE :email')->setParameter('email', '%'.$queryParams['email'].'%');
        } else {
            $queryParams['email'] = '';
        }

        if (!empty($queryParams['fio'])) {
            $qb->orWhere('(u.lastName LIKE :fio or u.firstName LIKE :fio or u.middleName LIKE :fio)')->setParameter('fio', '%'.$queryParams['fio'].'%');
        } else {
            $queryParams['fio'] = '';
        }
        if (!empty($queryParams['company'])) {
            $qb->innerJoin('u.company', 'c');
            $qb->orWhere('c.title LIKE :title and c.isDeleted = 0')->setParameter('title', '%'.$queryParams['company'].'%');
        } else {
            $queryParams['company'] = '';
        }
        if (!empty($queryParams['role'])) {
            /** @var Role[] $tmpR */
            $tmpR = $this->entityManager->getRepository(Role::class)->createQueryBuilder('role', 'role.id')->getQuery()->getResult();
            if (isset($tmpR[$queryParams['role']])) {
                $qb->innerJoin('u.userRole', 'r');
                $qb->orWhere('r.roleName = :role')->setParameter('role', $tmpR[$queryParams['role']]->getTitle());
            }
        } else {
            $queryParams['role'] = '';
        }

        $totalRows = (new \Doctrine\ORM\Tools\Pagination\Paginator($qb->getQuery()))->count();

        $paginator = [
            'countItems'   => $totalRows,
            'query'        => $queryParams,
            'currentPage'  => $page,
            'itemsPerPage' => self::COUNT_ITEMS,
            'url'          => $this->urlHelper->generate('admin.users'),
        ];

        $params = [
            'id',
            'lastName',
            'email',
            'phone',
            'userRole',
            'dateCreate',
            'dateLastAuth',
            'isConfirmed',
        ];

        $field = in_array($sortType, $params) ? $sortType : 'id';

        $users = $qb->setMaxResults(self::COUNT_ITEMS)
            ->orderBy('u.'.$field, $orderCheck)
            ->setFirstResult(self::COUNT_ITEMS * ($page - 1))
            ->getQuery()->getResult();

//        $users = $this->entityManager->getRepository(User::class)->findBy([]);


        return new HtmlResponse(
            $this->template->render(
                self::TEMPLATE_NAME,
                [
                    'sortType'  => $field,
                    'order'     => $orderCheck === 'ASC' ? 'DESC' : 'ASC',
                    'chevron'   => $orderCheck === 'ASC' ? 'up' : 'down',
                    'users'     => $users,
                    'roles'     => $roles,
                    'paginator' => $paginator,
                ]
            )
        );
    }
}
