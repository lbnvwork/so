<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 19.01.18
 * Time: 10:52
 */

namespace Auth\Action;

use App\Helper\UrlHelper;
use App\Service\FlashMessage;
use Auth\Service\AuthenticationService;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Session\LazySession;
use Zend\Expressive\Session\SessionMiddleware;

/**
 * Class RollbackAction
 *
 * @package Auth\Action
 */
class RollbackAction implements ServerMiddlewareInterface
{
    private $urlHelper;

    /**
     * RollbackAction constructor.
     *
     * @param UrlHelper $urlHelper
     */
    public function __construct(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return HtmlResponse|RedirectResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        /** @var LazySession $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        if ($session && $session->get('rollback')) {
            $session->set(AuthenticationService::SESSION_AUTH, $session->get('rollback'));
            $session->unset('rollback');

            /** @var FlashMessage $flashMessage */
            $flashMessage = $request->getAttribute(FlashMessage::class);
            $flashMessage->addSuccessMessage('Вы вернулись в админку');

            return new RedirectResponse($this->urlHelper->generate('admin.users'));
        }

        return new RedirectResponse($this->urlHelper->generate('home'));
    }
}
