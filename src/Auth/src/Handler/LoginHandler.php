<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 19.01.18
 * Time: 10:52
 */

declare(strict_types=1);

namespace Auth\Handler;

use App\Helper\UrlHelper;
use App\Service\DateTime;
use App\Service\FlashMessage;
use Auth\Entity\User;
use Auth\Service\AuthenticationService;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Authentication\UserRepositoryInterface;
use Zend\Expressive\Session\LazySession;
use Zend\Expressive\Session\SessionMiddleware;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class LoginHandler
 *
 * @package Auth\Handler
 */
class LoginHandler implements RequestHandlerInterface
{
    private $auth;

    private $template;

    private $urlHelper;

    private $entityManager;

    /**
     * LoginAction constructor.
     *
     * @param TemplateRendererInterface $template
     * @param UserRepositoryInterface $auth
     * @param UrlHelper $urlHelper
     * @param EntityManager $entityManager
     */
    public function __construct(TemplateRendererInterface $template, UserRepositoryInterface $auth, UrlHelper $urlHelper, EntityManager $entityManager)
    {
        $this->template = $template;
        $this->auth = $auth;
        $this->urlHelper = $urlHelper;
        $this->entityManager = $entityManager;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            return $this->authenticate($request);
        }

        return new HtmlResponse($this->template->render('auth::login', ['layout' => 'layout::auth']));
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function authenticate(ServerRequestInterface $request)
    {
        $params = $request->getParsedBody();
        $user = null;
        $message = null;

        if (empty($params['username']) || empty($params['password'])) {
            $message = 'Не корректно заполнены поля';
        } else {
            /** @var User $user */
            $user = $this->auth->authenticate($params['username'], $params['password']);
            if ($user === null) {
                $message = 'Неверный логин/пароль';
            } elseif (!$user->getIsConfirmed()) {
                $message = 'Необходимо подтвердить email';
            }
        }

        if ($message !== null) {
            /** @var FlashMessage $flash */
            $flash = $request->getAttribute(FlashMessage::class);
            $flash->addErrorMessage($message);

            return new RedirectResponse($this->urlHelper->generate('login'));
        }

        $user->setDateLastAuth(new DateTime());
        $this->entityManager->flush();

        /** @var LazySession $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $session->set(AuthenticationService::SESSION_AUTH, $user->getId());

        /*
        if ($user->getUserRoleManager()->offsetExists('manager') || $user->getUserRoleManager()->offsetExists('admin')) {
            return new RedirectResponse($this->urlHelper->generate('admin.users'));
        }

        return new RedirectResponse($this->urlHelper->generate('office.company'));
        **/

        return new RedirectResponse($this->urlHelper->generate('home'));
    }
}
