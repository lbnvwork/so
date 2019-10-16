<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 26.03.18
 * Time: 21:20
 */

namespace Office\Action\Billing;

use App\Helper\UrlHelper;
use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Template;

/**
 * Class ReferralAction
 *
 * @package Office\Action\Billing
 */
class ReferralAction implements ServerMiddlewareInterface
{
    public const COUNT_ITEMS = 20;

    private $entityManager;

    private $template;

    private $urlHelper;

    /**
     * ReferralAction constructor.
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
            'u.id',
            'u.lastName',
            'u.dateCreate',
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

        $filter2 = ['name' => ''];

        $page = isset($queryParams['page']) && $queryParams['page'] > 0 ? $queryParams['page'] : 1;

        $qb = $this->entityManager->createQueryBuilder()->select('u, c')->from(User::class, 'u')->innerJoin('u.company', 'c');

        $searchArr = ['u.referral = :user'];
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
        }
        $qb->where(implode(' AND ', $searchArr));

        $totalRows = (new \Doctrine\ORM\Tools\Pagination\Paginator($qb->getQuery()))->count();

        $paginator = [
            'countItems'   => $totalRows,
            'query'        => $queryParams,
            'currentPage'  => $page,
            'itemsPerPage' => self::COUNT_ITEMS,
            'url'          => $this->urlHelper->generate('office.referral')
        ];


        $referrals = $qb->setMaxResults(self::COUNT_ITEMS)
            ->orderBy($field, $orderCheck)
            ->setFirstResult(self::COUNT_ITEMS * ($page - 1))
            ->getQuery()->getResult();

        return new HtmlResponse(
            $this->template->render(
                'office::billing/referrals',
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
