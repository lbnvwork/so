<?php


namespace ApiInsales\Handler;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Template;

class ManualHandler implements MiddlewareInterface
{
    private $template;

    /**
     * ManualHandler constructor.
     *
     * @param Template\TemplateRendererInterface $template
     */
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
        return new Response\HtmlResponse(
            $this->template->render(
                'insales::manual',
                [
                    'layout' => 'layout::auth',
                ]
            )
        );
    }
}
