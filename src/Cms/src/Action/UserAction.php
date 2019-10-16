<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 24.01.18
 * Time: 14:09
 */

namespace Cms\Action;

use App\Service\FlashMessage;
use Auth\Entity\User;
use Auth\Entity\UserHasRole;
use Auth\Service\AuthenticationService;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Permission\Entity\Role;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Session\LazySession;
use Zend\Expressive\Session\SessionMiddleware;

/**
 * Class UserAction
 *
 * @package Cms\Action\Super
 */
class UserAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::user';

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|Response|HtmlResponse|Response\RedirectResponse|static
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        $id = $request->getAttribute('id');
        $user = null;
        if (empty($id)) {
            $user = new User();
        } else {
            /** @var User $user */
            $user = $this->entityManager->find(User::class, $id);
        }

        if ($user === null) {
            return (new Response())->withStatus(404);
        }

        if ($request->getMethod() === 'POST') {
            return $this->saveUser($request, $user);
        }

        if ($request->getAttribute('action') === 'auth' && !$user->getUserRoleManager()->offsetExists('admin')) {
            /** @var LazySession $session */
            $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
            $session->set(AuthenticationService::SESSION_AUTH, $user->getId());
            $session->set('rollback', $request->getAttribute(UserInterface::class)->getId());
            /** @var FlashMessage $flashMessage */
            $flashMessage = $request->getAttribute(FlashMessage::class);
            $flashMessage->addWarningMessage('Вы авторизованны под пользователем '.$user->getFIO().', будьте внимательны!');

            return new Response\RedirectResponse($this->urlHelper->generate('office.billing'));
        }

        $roles = $this->entityManager->getRepository(Role::class)->findAll();
        $userRoles = [];

        foreach ($user->getUserRoles() as $role) {
            $userRoles[] = $role->getRoleName();
        }

        return new HtmlResponse(
            $this->template->render(
                self::TEMPLATE_NAME,
                [
                    'editUser'  => $user,
                    'roles'     => $roles,
                    'userRoles' => $userRoles,
                ]
            )
        );
    }

    /**
     * Сохранение пользователя
     *
     * @param ServerRequestInterface $request
     * @param User $user
     *
     * @return Response\RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveUser(ServerRequestInterface $request, User $user): Response\RedirectResponse
    {
        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);

        $params = $request->getParsedBody();

        $user->setLastName($params['lastName']);
        $user->setFirstName($params['firstName']);
        $user->setMiddleName($params['middleName']);
        $user->setEmail($params['email']);
        $user->setIsConfirmed(isset($params['isConfirmed']));

        if ($user->getId() === null) {
            $user->setIsBeginner(true);
        }

        if ($user->getId() === null && empty($params['password'])) {
            $flashMessage->addMessage(FlashMessage::ERROR, 'Поле пароль не заполнено');

            return new Response\RedirectResponse($this->urlHelper->generate('admin.user', ['id' => (int)$user->getId()]));
        }

        if (!empty($params['password'])) {
            if ($params['password'] === $params['password2']) {
                $user->setNewPassword($params['password']);
            } else {
                $flashMessage->addMessage(FlashMessage::ERROR, 'Пароли не совпадают');

                return new Response\RedirectResponse($this->urlHelper->generate('admin.user', ['id' => (int)$user->getId()]));
            }
        }

        if ($user->getId() === null) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $this->updateUserRole($params['userRoles'], $user);

        $this->entityManager->flush();

        $flashMessage->addSuccessMessage('Данные пользователя сохранены');

        return new Response\RedirectResponse($this->urlHelper->generate('admin.user', ['id' => (int)$user->getId()]));
    }

    /**
     * Сохранение ролей пользователя
     *
     * @param array $roles
     * @param User $user
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateUserRole(array $roles, User $user): void
    {
        $userRoles = $user->getUserRoles();
        foreach ($userRoles as $key => $role) {
            if (!\in_array($role->getRoleName(), $roles, true)) {
                $this->entityManager->remove($role);
            } else {
                unset($roles[array_search($role->getRoleName(), $roles, true)]);
            }
        }

        foreach ($roles as $role) {
            $user->getUserRoleManager()->add((new UserHasRole())->setUser($user)->setRoleName($role));
        }

        $this->entityManager->flush();
    }
}
