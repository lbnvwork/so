<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 12.04.18
 * Time: 21:19
 */

namespace Office\Action\Check;

use App\Helper\UrlHelper;
use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\Processing;
use Office\Entity\Shop;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Template;

/**
 * Class ListAction
 *
 * @package Office\Action\Check
 */
class ListAction implements ServerMiddlewareInterface
{
    public const COUNT_ITEMS = 20;

    private $entityManager;

    private $template;

    private $urlHelper;

    /**
     * ListAction constructor.
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
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $page = isset($queryParams['page']) && $queryParams['page'] > 0 ? $queryParams['page'] : 1;

        /** @var User $user */
        $user = $request->getAttribute(UserInterface::class);

        $qb = $this->entityManager->getRepository(Processing::class)->createQueryBuilder('p');

        $filter = $queryParams['filter'] ?? [
                'status'  => 0,
                'type'    => 0,
                'company' => '',
            ];
        if (isset($queryParams['filter'])) {
            if (!empty($queryParams['filter']['status'])) {
                $qb->andWhere('p.status = :status')->setParameter('status', $queryParams['filter']['status']);
            }
            if (!empty($queryParams['filter']['type'])) {
                $qb->andWhere('p.operation = :type')->setParameter('type', $queryParams['filter']['type']);
            }
        }


        if (!$user->getUserRoleManager()->offsetExists('admin') && !$user->getUserRoleManager()->offsetExists('manager')) {
            $companies = $user->getCompany();
            $shops = $this->entityManager->getRepository(Shop::class)->createQueryBuilder('s')
                ->where('s.company in(:companies)')
                ->setParameter('companies', $companies)
                ->getQuery()->getResult();

            $qb->andWhere('p.shop in(:shop)')->setParameter('shop', $shops);
        }

        $totalRows = (new \Doctrine\ORM\Tools\Pagination\Paginator($qb->getQuery()))->count();

        $paginator = [
            'countItems'   => $totalRows,
            'query'        => $queryParams,
            'currentPage'  => $page,
            'itemsPerPage' => self::COUNT_ITEMS,
            'url'          => $this->urlHelper->generate('office.check'),
        ];

        $checks = $qb->setMaxResults(self::COUNT_ITEMS)
            ->setFirstResult(self::COUNT_ITEMS * ($page - 1))
            ->orderBy('p.id', 'DESC')
            ->getQuery()->getResult();

        return new HtmlResponse(
            $this->template->render(
                'office::check/list',
                [
                    'checks'    => $checks,
                    'paginator' => $paginator,
                    'filter'    => $filter,
                ]
            )
        );
    }
}
