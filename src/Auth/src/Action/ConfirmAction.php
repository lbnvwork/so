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
use Auth\Service\AuthenticationService;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Session\LazySession;
use Zend\Expressive\Session\SessionMiddleware;

/**
 * Class ConfirmAction
 *
 * @package Auth\Action
 */
class ConfirmAction implements ServerMiddlewareInterface
{
    private $entityManager;

    private $urlHelper;

    /**
     * ConfirmAction constructor.
     *
     * @param EntityManager $entityManager
     * @param UrlHelper $urlHelper
     */
    public function __construct(EntityManager $entityManager, UrlHelper $urlHelper)
    {
        $this->entityManager = $entityManager;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        /** @var FlashMessage $flashMeaasge */
        $flashMeaasge = $request->getAttribute(FlashMessage::class);
        $hash = $request->getAttribute('hash');
        if ($hash !== null) {
            /** @var User $user */
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['hashKey' => $hash]);
            if ($user !== null) {
//                $user->setHashKey(null);
//                $user->setIsConfirmed(true);
//                $this->entityManager->persist($user);
//                $this->entityManager->flush();
                /** @var LazySession $session */
                $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
                $session->set(AuthenticationService::SESSION_AUTH, $user->getId());

                $flashMeaasge->addSuccessMessage('E-mail подтвержден');
            } else {
                $flashMeaasge->addErrorMessage('Неверный код');
            }
        }

        return new RedirectResponse($this->urlHelper->generate('home'));
    }
}
