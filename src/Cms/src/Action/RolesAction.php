<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 25.01.18
 * Time: 14:07
 */

namespace Cms\Action;

use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Permission\Entity\Role;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class RolesAction
 *
 * @package Cms\Action
 */
class RolesAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::roles';

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return HtmlResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        $sortType = $request->getQueryParams()['sort'] ?? 'id';
        $order = $request->getQueryParams()['order'] ?? 'ASC';
        $orderCheck = in_array($order, ['ASC', 'DESC']) ? $order : 'ASC';
        $orderType = $orderCheck === 'ASC' ? 'DESC' : 'ASC';
        $chevron = $orderType === 'ASC' ? 'down' : 'up';

        $params = [
            'id',
            'roleName',
            'title'
        ];

        $field = in_array($sortType, $params) ? $sortType : 'id';
        $roles = $this->entityManager->getRepository(Role::class)
            ->findBy([], [$field => $orderCheck]);

        return new HtmlResponse(
            $this->template->render(
                self::TEMPLATE_NAME,
                [
                    'sortType' => $field,
                    'order' => $orderType,
                    'chevron' => $chevron,
                    'roles' => $roles
                ]
            )
        );
    }
}
