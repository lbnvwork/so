<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 28.03.18
 * Time: 12:01
 */

namespace Auth\Action;

use App\Helper\UrlHelper;
use App\Service\FlashMessage;
use Auth\Entity\User;
use Auth\UserRepository\Database;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Template;

/**
 * Class ChangePasswordAction
 *
 * @package Cms\Action\User
 */
class ChangePasswordAction implements ServerMiddlewareInterface
{
    public const TEMPLATE_NAME = 'auth::change-password';

    private $urlHelper;

    private $template;

    private $entityManager;

    private $database;

    /**
     * ChangePasswordAction constructor.
     *
     * @param EntityManager $entityManager
     * @param Database $database
     * @param UrlHelper $urlHelper
     * @param Template\TemplateRendererInterface $template
     */
    public function __construct(EntityManager $entityManager, Database $database, UrlHelper $urlHelper, Template\TemplateRendererInterface $template)
    {
        $this->entityManager = $entityManager;
        $this->template = $template;
        $this->urlHelper = $urlHelper;
        $this->database = $database;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|HtmlResponse|RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);
        if ($request->getMethod() === 'POST') {
            $params = $request->getParsedBody();
            if (!empty($params['oldPassword']) && !empty($params['newPassword']) && $params['newPassword'] === $params['confirmPassword']) {
                /** @var User $user */
                $user = $request->getAttribute(UserInterface::class);
                if ($this->database->verifyPassword($user, $params['oldPassword'])) {
                    $user->setNewPassword($params['newPassword']);

                    if ($user->getId() !== User::TEST_USER_ID) {
                        $this->entityManager->persist($user);
                        $this->entityManager->flush();
                    }
                    $flashMessage->addSuccessMessage('Данные обновлены');
                } else {
                    $flashMessage->addErrorMessage('Не верный пароль');
                }
            } else {
                $flashMessage->addErrorMessage('Не корректно заполнены поля');
            }

            return new RedirectResponse($this->urlHelper->generate('user.changePassword'));
        }

        return new HtmlResponse($this->template->render(self::TEMPLATE_NAME));
    }
}
