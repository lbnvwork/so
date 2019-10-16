<?php

namespace Office\Action;

use App\Helper\UrlHelper;
use Auth\Entity\User;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Router;
use Zend\Expressive\Template;

/**
 * Class HomePageAction
 *
 * @package Office\Action
 */
class HomePageAction implements ServerMiddlewareInterface
{
    private $router;

    private $template;
    private $urlHelper;

    public function __construct(Router\RouterInterface $router, Template\TemplateRendererInterface $template = null, UrlHelper $urlHelper)
    {
        $this->router   = $router;
        $this->template = $template;
        $this->urlHelper = $urlHelper;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
//        return new HtmlResponse($this->template->render('office::index-page', []));
        /** @var User $user */
        /*
        $user = $request->getAttribute(UserInterface::class);

        if ($user->getUserRoleManager()->offsetExists('manager') || $user->getUserRoleManager()->offsetExists('admin')) {
            return new RedirectResponse($this->urlHelper->generate('admin.users'));
        }

        return new RedirectResponse($this->urlHelper->generate('office.company'));
        */
        return new RedirectResponse($this->urlHelper->generate('home'));
    }
}
