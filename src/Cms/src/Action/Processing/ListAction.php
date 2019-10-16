<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 25.01.18
 * Time: 14:07
 */

declare(strict_types=1);

namespace Cms\Action\Processing;

use Cms\Action\AbstractAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Office\Entity\Processing;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class ListAction
 *
 * @package Cms\Action\Processing
 */
class ListAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::processing/list';
    public const COUNT_ITEMS = 50;

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        $page = isset($queryParams['page']) && $queryParams['page'] > 0 ? $queryParams['page'] : 1;

        $qb = $this->entityManager->getRepository(Processing::class)->createQueryBuilder('p');

        $filter = $queryParams['filter'] ?? [
                'status'  => 0,
                'type'    => 0,
                'company' => ''
            ];
        if (isset($queryParams['filter'])) {
            if (!empty($queryParams['filter']['status'])) {
                $qb->andWhere('p.status = :status')->setParameter('status', $queryParams['filter']['status']);
            }
            if (!empty($queryParams['filter']['type'])) {
                $qb->andWhere('p.operation = :type')->setParameter('type', $queryParams['filter']['type']);
            }
            if (!empty($queryParams['filter']['company'])) {
                $qb->leftJoin('p.shop', 's')
                    ->leftJoin('s.company', 'c')
                    ->andWhere('c.title LIKE :title')->setParameter('title', '%'.$queryParams['filter']['company'].'%');
            }
        }

        $totalRows = (new \Doctrine\ORM\Tools\Pagination\Paginator($qb->getQuery()))->count();

        $paginator = [
            'countItems'   => $totalRows,
            'query'        => $queryParams,
            'currentPage'  => $page,
            'itemsPerPage' => self::COUNT_ITEMS,
            'url'          => $this->urlHelper->generate('admin.processing')
        ];

        #$qb->orderBy('i.date', 'DESC');
        $qb->orderBy('p.id', 'DESC');
        $items = $qb->setMaxResults(self::COUNT_ITEMS)->setFirstResult(self::COUNT_ITEMS * ($page - 1))->getQuery()->getResult();

        return new HtmlResponse(
            $this->template->render(
                self::TEMPLATE_NAME,
                [
                    'items'     => $items,
                    'paginator' => $paginator,
                    'filter'    => $filter
                ]
            )
        );
    }
}
