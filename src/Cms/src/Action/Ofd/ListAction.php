<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 29.03.18
 * Time: 20:33
 */

namespace Cms\Action\Ofd;

use Cms\Action\AbstractAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Office\Entity\Ofd;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class ListAction
 *
 * @package Cms\Action\Ofd
 */
class ListAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::ofd/list';

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface|HtmlResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $sortType = $request->getQueryParams()['sort'] ?? 'id';
        $order = $request->getQueryParams()['order'] ?? 'ASC';
        $orderCheck = in_array(
            $order,
            [
                'ASC',
                'DESC',
            ]
        ) ? $order : 'ASC';
        $orderType = ($orderCheck === 'ASC') ? 'DESC' : 'ASC';
        $chevron = $orderType === 'ASC' ? 'down' : 'up';

        $params = [
            'id',
            'title',
            'isEnabled',
        ];

        $field = in_array($sortType, $params) ? $sortType : 'id';
        $items = $this->entityManager->getRepository(Ofd::class)
            ->findBy([], [$field => $orderCheck]);

        return new HtmlResponse(
            $this->template->render(
                self::TEMPLATE_NAME,
                [
                    'sortType' => $field,
                    'order'    => $orderType,
                    'chevron'  => $chevron,
                    'items'    => $items,
                ]
            )
        );
    }
}
