<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 19.01.18
 * Time: 16:42
 */

namespace Auth\Action;

use App\Helper\UrlHelper;
use Auth\Service\AuthenticationService;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Session\LazySession;
use Zend\Expressive\Session\SessionMiddleware;

/**
 * Class LogoutAction
 *
 * @package Auth\Action
 */
class LogoutAction implements ServerMiddlewareInterface
{
    private $urlHelper;

    /**
     * LogoutAction constructor.
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
     * @return \Psr\Http\Message\ResponseInterface|RedirectResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        /** @var LazySession $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $session->unset(AuthenticationService::SESSION_AUTH);

        return new RedirectResponse($this->urlHelper->generate('home'));
    }
}
