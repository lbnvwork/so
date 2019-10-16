<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 25.01.18
 * Time: 14:07
 */

namespace Cms\Action;

use App\Service\FlashMessage;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Permission\Entity\Role;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class RoleAction
 *
 * @package Cms\Action
 */
class RoleAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::role';

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $id = $request->getAttribute('id');
        $role = null;
        if (empty($id)) {
            $role = new Role();
        } else {
            $role = $this->entityManager->find(Role::class, $id);
        }

        if ($role === null) {
            return (new Response())->withStatus(404);
        }

        if ($request->getMethod() === 'POST') {
            return $this->saveRole($request, $role);
        }

        return new HtmlResponse($this->template->render(self::TEMPLATE_NAME, ['role' => $role]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param Role $role
     *
     * @return Response\RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveRole(ServerRequestInterface $request, Role $role)
    {
        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);
        $params = $request->getParsedBody();

        $role->setRoleName($params['rolename']);
        $role->setTitle($params['title']);
        $this->entityManager->persist($role);
        $this->entityManager->flush();

        $flashMessage->addSuccessMessage('Роль сохранена');

        return new Response\RedirectResponse($this->urlHelper->generate('admin.role', ['id' => $role->getId()]));
    }
}
