<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 25.01.18
 * Time: 14:07
 */

namespace Cms\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Office\Entity\ApiKey;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class ApiTokenAction
 *
 * @package Cms\Action
 */
class ApiTokenAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::api-token';

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return HtmlResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
    {
        $sortType = $request->getQueryParams()['sort'] ?? 'id';
        $order = $request->getQueryParams()['order'] ?? 'ASC';
        $orderCheck = in_array($order, ['ASC', 'DESC']) ? $order : 'ASC';
        $orderType = $orderCheck === 'ASC' ? 'DESC' : 'ASC';
        $chevron = $orderType === 'ASC' ? 'down' : 'up';

        $params = [
            'id',
            'token',
            'dateExpiredToken'
        ];

        $field = in_array($sortType, $params) ? $sortType : 'id';
        $tokens = $this->entityManager->getRepository(ApiKey::class)
            ->findBy([], [$field => $orderCheck]);

        return new HtmlResponse(
            $this->template->render(
                self::TEMPLATE_NAME,
                [
                    'sortType' => $field,
                    'order' => $orderType,
                    'chevron' => $chevron,
                    'tokens' => $tokens
                ]
            )
        );
    }
}
