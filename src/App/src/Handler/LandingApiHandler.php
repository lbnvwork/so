<?php
declare(strict_types=1);

namespace App\Handler;

use App\Helper\UrlHelper;
use App\Service\SendMail;
use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use App\Service\RecaptchaService;

/**
 * Class LandingApiHandler
 *
 * @package App\Action
 */
class LandingApiHandler implements ServerMiddlewareInterface
{
    private $sendMail;

    private $entityManager;

    private $urlHelper;

    private $recaptchaService;

    /**
     * LandingApiHandler constructor.
     *
     * @param SendMail $_sendMail
     * @param EntityManager $entityManager
     * @param UrlHelper $urlHelper
     * @param RecaptchaService $_recaptcha
     */
    public function __construct(SendMail $_sendMail, EntityManager $entityManager, UrlHelper $urlHelper, RecaptchaService $_recaptcha)
    {
        $this->sendMail = $_sendMail;
        $this->entityManager = $entityManager;
        $this->urlHelper = $urlHelper;
        $this->recaptchaService = $_recaptcha;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        $params = $request->getParsedBody();
        $action = $request->getAttribute('action');
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $params['email']]);

        if ($action === 'promo') {
            if (isset($params['email'])) {
                $this->sendMail->sendPromoStepOne($params['email']);
                if ($user && $user->getRoboPromo() !== 2) {
                    $user->setRoboPromo(1);
                    $this->entityManager->flush();
                }
                $delegate->handle($request->withAttribute('needPromo', true));
            }

            return new JsonResponse(['success' => true]);
        }

        $recaptchaResponse = null;

        if ($action === 'cms') {
            if (isset($params['g-recaptcha-response'])) {
                $recaptchaResponse = $this->recaptchaService->getRecaptcha()->verify(
                    $params['g-recaptcha-response']
                );
            }
            if ($recaptchaResponse != null && $recaptchaResponse->isValid()) {
                $this->sendMail->sendNeedCms($params);

                return new JsonResponse(['success' => true]);
            }

            return new JsonResponse(['success' => false]);
        }

        if ($action === 'register') {
            if ($user) {
                return new JsonResponse(
                    [
                        'success' => false,
                        'msg'     => 'Пользователь с таким email уже зарегистрирован, 
                            <a href="'.$this->urlHelper->generate('login').'">авторизуйтесь</a> или <a href="'
                            .$this->urlHelper->generate('user.forget').'">восстановите пароль.</a>'
                    ]
                );
            }
            $delegate->handle($request);

            return new JsonResponse(
                [
                    'success' => true,
                    'msg'     => 'На Ваш email было отправлено письмо для подтверждения почтового ящика'
                ]
            );
        }

        return new JsonResponse(['success' => true]);
    }
}
