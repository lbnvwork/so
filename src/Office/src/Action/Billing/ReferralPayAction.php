<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 18.09.18
 * Time: 9:18
 */

namespace Office\Action\Billing;

use App\Helper\UrlHelper;
use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\ReferralPayment;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Template;

/**
 * Class ReferralPayAction
 *
 * @package Office\Action\Billing
 */
class ReferralPayAction implements ServerMiddlewareInterface
{
    public const COUNT_ITEMS = 20;

    private $entityManager;

    private $template;

    private $urlHelper;

    /**
     * ReferralPayAction constructor.
     *
     * @param EntityManager $entityManager
     * @param Template\TemplateRendererInterface $template
     * @param UrlHelper $urlHelper
     */
    public function __construct(EntityManager $entityManager, Template\TemplateRendererInterface $template, UrlHelper $urlHelper)
    {
        $this->entityManager = $entityManager;
        $this->template = $template;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return Response|HtmlResponse|static
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute(UserInterface::class);

        $queryParams = $request->getQueryParams();

        $params = [
            'r.id',
            'r.sum',
            'r.pay',
            'u.lastName',
            'r.datetime',
            'c.title'
        ];

        $field = isset($queryParams['sort']) && in_array($queryParams['sort'], $params) ? $queryParams['sort'] : 'u.id';

        $orderCheck = isset($queryParams['order'])
        && in_array(
            $queryParams['order'],
            [
            'DESC',
            'ASC'
            ]
        ) ? $queryParams['order'] : 'ASC';

        $orderType = $orderCheck === 'ASC' ? 'DESC' : 'ASC';
        $chevron = $orderType === 'ASC' ? 'down' : 'up';

        $filter = $queryParams['filter'] ?? false;
        $filter2 = [
            'name' => '',
            'pay'  => 100
        ];


        $page = isset($queryParams['page']) && $queryParams['page'] > 0 ? $queryParams['page'] : 1;

        $qb = $this->entityManager->createQueryBuilder()
            ->select('u, c, r')
            ->from(ReferralPayment::class, 'r')
            ->innerJoin('r.company', 'c')
            ->innerJoin('r.fromUser', 'u');


        $searchArr = ['r.user = :user'];
        $qb->setParameter('user', $user);

        if ($filter && is_array($filter)) {
            if (isset($filter['name']) && !empty($filter['name'])) {
                $search = preg_replace('/[^a-zA-Zа-яА-Я0-9\s.,]/ui', ' ', (string)$filter['name']);
                $search = preg_replace('|\s+|', ' ', $search);
                $search = $filter2['name'] = trim($search);
                $search2 = '%'.str_replace(' ', '%', $search).'%';
                $searchArr[] = ' (u.lastName LIKE :name OR c.title LIKE :name) ';
                $qb->setParameter('name', $search2);
            }

            if (isset($filter['pay'])
                && in_array(
                    $filter['pay'],
                    [
                        '0',
                        '1'
                    ]
                )) {
                $filter2['pay'] = $filter['pay'];
                $searchArr[] = 'r.pay = :pay';
                $qb->setParameter('pay', $filter['pay']);
            }
        }
        $qb->where(implode(' AND ', $searchArr));


        $totalRows = (new \Doctrine\ORM\Tools\Pagination\Paginator($qb->getQuery()))->count();

        $paginator = [
            'countItems'   => $totalRows,
            'query'        => $queryParams,
            'currentPage'  => $page,
            'itemsPerPage' => self::COUNT_ITEMS,
            'url'          => $this->urlHelper->generate('office.referralpay')
        ];


        $referrals = $qb->setMaxResults(self::COUNT_ITEMS)
            ->orderBy($field, $orderCheck)
            ->setFirstResult(self::COUNT_ITEMS * ($page - 1))
            ->getQuery()->getResult();

        return new HtmlResponse(
            $this->template->render(
                'office::billing/referrals-pay',
                [
                    'referrals' => $referrals,
                    'user'      => $user,
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
