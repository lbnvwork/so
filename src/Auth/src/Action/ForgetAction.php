<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 19.01.18
 * Time: 16:42
 */

namespace Auth\Action;

use App\Helper\UrlHelper;
use App\Service\FlashMessage;
use Auth\Entity\User;
use Auth\Service\SendMail;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class ForgetAction
 *
 * @package Auth\Action
 */
class ForgetAction implements ServerMiddlewareInterface
{
    private $entityManager;

    private $template;

    private $sendMail;

    private $urlHelper;

    /**
     * ForgetAction constructor.
     *
     * @param TemplateRendererInterface $template
     * @param EntityManager $entityManager
     * @param SendMail $sendMail
     * @param UrlHelper $urlHelper
     */
    public function __construct(TemplateRendererInterface $template, EntityManager $entityManager, SendMail $sendMail, UrlHelper $urlHelper)
    {
        $this->entityManager = $entityManager;
        $this->template = $template;
        $this->sendMail = $sendMail;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|RedirectResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        /** @var FlashMessage $flash */
        $flash = $request->getAttribute(FlashMessage::class);
        $params = $request->getParsedBody();
        if (!empty($params['email'])) {
            /** @var User $user */
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $params['email']]);
            if ($user !== null) {
                $user->setHashKey(str_replace('.', '', uniqid(time(), true)));
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $this->sendMail->restorePassword($user);
                $flash->addSuccessMessage('На Ваш e-mail была отправлена инструкция по восстановленю пароля');
            } else {
                $flash->addErrorMessage('E-mail не найден');
            }

            return new RedirectResponse($this->urlHelper->generate('user.forget'));
        }

        return new HtmlResponse($this->template->render('auth::forget', ['layout' => 'layout::auth']));
    }
}
