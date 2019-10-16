<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 05.07.18
 * Time: 11:49
 */

namespace Cms\Action\Tariff;

use Cms\Action\AbstractAction;
use Office\Entity\Tariff;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class ListAction
 *
 * @package Cms\Action\Service
 */
class ListAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::tariff/list';

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return HtmlResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $sortType = $request->getQueryParams()['sort'] ?? 'sort';
        $order = $request->getQueryParams()['order'] ?? 'ASC';
        $orderCheck = in_array(
            $order,
            [
                'ASC',
                'DESC',
            ]
        ) ? $order : 'ASC';
        $orderType = $orderCheck === 'ASC' ? 'DESC' : 'ASC';
        $chevron = $orderType === 'ASC' ? 'down' : 'up';
        $params = [
            'id',
            'sort',
            'title',
            'description',
            'rentCost',
            'turnoverPercent',
            'monthLimit',
            'monthCount',
            'maxTurnover',
            'popular',
            'beginner',
        ];
        $field = in_array($sortType, $params) ? $sortType : 'sort';
        $tariffs = $this->entityManager->getRepository(Tariff::class)->findBy([], [$field => $orderCheck]);

        return new HtmlResponse(
            $this->template->render(
                self::TEMPLATE_NAME,
                [
                    'sortType' => $field,
                    'order'    => $orderType,
                    'chevron'  => $chevron,
                    'tariffs'  => $tariffs,
                ]
            )
        );
    }
}
