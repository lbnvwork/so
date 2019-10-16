<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 26.03.18
 * Time: 21:04
 */

namespace Office\Action;

use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Router;
use Zend\Expressive\Template;

/**
 * Class OfertaAction
 * @package Office\Action
 */
class OfertaAction implements ServerMiddlewareInterface
{
    private $router;

    private $template;

    public function __construct(Router\RouterInterface $router, Template\TemplateRendererInterface $template)
    {
        $this->router = $router;
        $this->template = $template;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        return new HtmlResponse($this->template->render('office::oferta', []));
    }
}
