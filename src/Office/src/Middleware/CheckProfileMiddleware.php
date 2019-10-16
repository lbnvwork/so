<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 26.09.18
 * Time: 13:47
 */

namespace Office\Middleware;

use App\Helper\UrlHelper;
use App\Service\FlashMessage;
use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Router\RouteResult;

/**
 * Class CheckProfileMiddleware
 *
 * @package Office\Middleware
 */
class CheckProfileMiddleware implements ServerMiddlewareInterface
{
    protected $entityManager;

    protected $urlHelper;

    /**
     * CheckProfileMiddleware constructor.
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
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute(UserInterface::class);

        if ($user && !$user->getIsConfirmed()) {
            /** @var FlashMessage $flashMessage */
            $flashMessage = $request->getAttribute(FlashMessage::class);
            $flashMessage->addInfoMessage('Необходимо заполнить профиль');

            return new RedirectResponse($this->urlHelper->generate('user.profile'));
        }

        if (!$user->getCompany()->count()) {
            $route = $request->getAttribute(RouteResult::class);
            if ($route && !$user->getUserRoleManager()->offsetExists('admin') && !$user->getUserRoleManager()->offsetExists('manager')) {
                /** @var FlashMessage $flashMessage */
                $flashMessage = $request->getAttribute(FlashMessage::class);
                $flashMessage->addInfoMessage('Чтобы начать соответствовать 54 ФЗ, заполните информацию о Вашей компании');

                $routeName = $route->getMatchedRoute()->getName();
                if ($routeName !== 'office.company.stepOne') {
                    return new RedirectResponse($this->urlHelper->generate('office.company.stepOne', ['id' => 0]));
                }
            }
        }

        return $delegate->handle($request);
    }
}
