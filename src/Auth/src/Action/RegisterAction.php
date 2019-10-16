<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 19.01.18
 * Time: 14:33
 */

namespace Auth\Action;

use App\Helper\UrlHelper;
use App\Service\FlashMessage;
use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Authentication\UserRepositoryInterface;
use Zend\Expressive\Session\LazySession;
use Zend\Expressive\Session\SessionMiddleware;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class RegisterAction
 *
 * @package Auth\Action
 */
class RegisterAction implements ServerMiddlewareInterface
{
    /** @var UserRepositoryInterface $auth */
    private $auth;

    private $template;

    private $entityManager;

    private $urlHelper;

    public const REGEXP_PHONE = '^(\+7|8)[\.\-\s]*\(?\d{3}\)?[\.\-\s]*\d{3}[\.\-\s]*\d{2}[\.\-\s]*\d{2}$';
    public const REGEXP_EMAIL = '^[\w\-\.\_]+@([\w\-]+\.)+[a-z]+$';
    /**
     * Описание полей формы
     */
    private const FIELDS = [
//        'lastName'   => ['title' => 'Фамилия'],
//        'firstName'  => ['title' => 'Имя'],
//        'middleName' => ['title' => 'Отчество'],
        'email' => [
            'title'    => 'Email',
            'required' => true,
            'regexp'   => self::REGEXP_EMAIL
        ],
//        'phone' => [
//            'title'    => 'Телефон',
//            'required' => false,
//            'regexp'   => self::REGEXP_PHONE
//        ],
    ];

    /**
     * RegisterAction constructor.
     *
     * @param TemplateRendererInterface $template
     * @param UserRepositoryInterface $auth
     * @param EntityManager $entityManager
     * @param UrlHelper $urlHelper
     */
    public function __construct(TemplateRendererInterface $template, UserRepositoryInterface $auth, EntityManager $entityManager, UrlHelper $urlHelper)
    {
        $this->template = $template;
        $this->auth = $auth;
        $this->entityManager = $entityManager;
        $this->urlHelper = $urlHelper;
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
        $queryParams = $request->getQueryParams();
        if (isset($queryParams['referral'])) {
            /** @var User|null $refUser */
            $refUser = $this->entityManager->getRepository(User::class)->find($queryParams['referral']);
            if ($refUser) {
                /** @var LazySession $session */
                $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
                $session->set('referral', $refUser->getId());
            }
        }

        if ($request->getMethod() === 'POST') {
            return $this->register($request);
        }

        return new HtmlResponse($this->template->render('auth::register', ['layout' => 'layout::auth']));
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return HtmlResponse|RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function register(ServerRequestInterface $request)
    {
        $params = $request->getParsedBody();

        $allowedParams = array_intersect_key($params, self::FIELDS);

        $messages = [];
        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);

        foreach (self::FIELDS as $key => $field) {
            if (empty($allowedParams[$key]) && $field['required']) {
                $messages[] = 'Не заполнено поле `'.$field['title'].'`';
            }
            if (isset($allowedParams[$key]) && !preg_match('#'.$field['regexp'].'#si', $allowedParams[$key])) {
                $messages[] = 'Поле '.$field['title'].' заполнено неверно!';
            }
        }
        if (\count($messages)
            || $this->entityManager->getRepository(User::class)->count(
                [
                    'email' => $allowedParams['email'],
                ]
            )) {
            if (\count($messages)) {
                foreach ($messages as $message) {
                    $flashMessage->addErrorMessage($message);
                }
            } else {
                $flashMessage->addErrorMessage('Пользователь с таким email уже зарегистрирован');
            }

            return new RedirectResponse($this->urlHelper->generate('register'));
        }

        /** @var User $user */
        $user = $this->auth->register($allowedParams);
        if ($request->getAttribute('needPromo')) {
            $user->setRoboPromo(1);
        }

        /** @var LazySession $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        if ($session->has('referral')) {
            /** @var User|null $refUser */
            $refUser = $this->entityManager->getRepository(User::class)->find($session->get('referral'));
            $user->setReferral($refUser);
        }
        $this->entityManager->flush();

        $flashMessage->addSuccessMessage('На Вашу почту было отправлено письмо для подтверждения почтового ящика и паролем для входа');

        return new RedirectResponse($this->urlHelper->generate('login'));
    }
}
