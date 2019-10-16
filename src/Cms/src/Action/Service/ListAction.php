<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 05.07.18
 * Time: 11:49
 */

namespace Cms\Action\Service;

use App\Entity\Service;
use Cms\Action\AbstractAction;
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
    public const TEMPLATE_NAME = 'admin::service/list';

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return HtmlResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
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
        $orderType = $orderCheck === 'ASC' ? 'DESC' : 'ASC';
        $chevron = $orderType === 'ASC' ? 'down' : 'up';

        $params = [
            'id',
            'name',
            'measure',
            'price',
            'defaultValue',
        ];
        $field = in_array($sortType, $params) ? $sortType : 'id';
        $services = $this->entityManager->getRepository(Service::class)
            ->findBy([], [$field => $orderCheck]);

        return new HtmlResponse(
            $this->template->render(
                self::TEMPLATE_NAME,
                [
                    'sortType' => $field,
                    'order'    => $orderType,
                    'chevron'  => $chevron,
                    'services' => $services,
                ]
            )
        );
    }
}
