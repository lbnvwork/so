<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 25.01.18
 * Time: 9:57
 */

namespace App\Middleware;

use App\Service\FlashMessage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Zend\Expressive\Session\SessionMiddleware;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Template;

/**
 * Class SlimFlashMiddleware
 *
 * @package App\Middleware
 */
class FlashMessageMiddleware implements MiddlewareInterface
{
    private $template;

    public function __construct(Template\TemplateRendererInterface $template)
    {
        $this->template = $template;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $flashMessages = new FlashMessage($session);
        $this->template->addDefaultParam(Template\TemplateRendererInterface::TEMPLATE_ALL, 'flashMessage', $flashMessages);

        return $handler->handle($request->withAttribute(FlashMessage::class, $flashMessages));
    }
}
