<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 24.01.18
 * Time: 11:18
 */

namespace Cms\Action;

use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Office\Entity\Fn;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class RfnAction
 *
 * @package Cms\Action
 */
class RfnAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::rfn';
    public const COUNT_ITEMS = 20;

    /** @var static final int TOTAL - всего чеков может напечатать фискальник */
    public const TOTAL = 300000;

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|HtmlResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        $order = $queryParams['order'] ?? 'ASC';
        $orderCheck = in_array(
            $order,
            [
            'ASC',
            'DESC'
            ]
        ) ? $order : 'ASC';

        $page = isset($queryParams['page']) && $queryParams['page'] > 0 ? $queryParams['page'] : 1;

        $qb = $this->entityManager->getRepository(Fn::class)
            ->createQueryBuilder('f');

        $totalRows = (new \Doctrine\ORM\Tools\Pagination\Paginator($qb->getQuery()))->count();

        $paginator = [
            'countItems' => $totalRows,
            'query' => $queryParams,
            'currentPage' => $page,
            'itemsPerPage' => self::COUNT_ITEMS,
            'url' => $this->urlHelper->generate('admin.rfn')
        ];

        $params = [
            'id',
            'company',
            'serialNumber',
            'fnNumber',
            'fnVersion',
            'status',
            'dateFiscalized',
            'dateDeleted',
            'documentNumber'
        ];

        $sortType = $queryParams['sort'] ?? 'id';
        $field = in_array($sortType, $params) ? $sortType : 'id';

        $rfns = $qb->setMaxResults(self::COUNT_ITEMS)
            ->orderBy('f.'.$field, $orderCheck)
            ->setFirstResult(self::COUNT_ITEMS * ($page - 1))
            ->getQuery()->getResult();

        return new HtmlResponse(
            $this->template->render(
                self::TEMPLATE_NAME,
                [
                    'sortType' => $field,
                    'order' => $orderCheck === 'ASC' ? 'DESC' : 'ASC',
                    'chevron' => $orderCheck === 'ASC' ? 'up' : 'down',
                    'rfns' => $rfns,
                    'paginator' => $paginator
                ]
            )
        );
    }
}
