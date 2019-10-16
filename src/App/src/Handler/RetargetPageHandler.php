<?php
declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use Doctrine\ORM\EntityManager;
use App\Helper\UrlHelper;

/**
 * Class RetargetPageHandler
 *
 * @package App\Handler
 */
class RetargetPageHandler implements RequestHandlerInterface
{
    private $template;

    public const TEMPLATE_NAME = 'app::retarget-page';

    /**
     * RetargetPageHandler constructor.
     *
     * @param EntityManager $entityManager
     * @param Template\TemplateRendererInterface $template
     * @param UrlHelper $urlHelper
     */
    public function __construct(EntityManager $entityManager, Template\TemplateRendererInterface $template, UrlHelper $urlHelper)
    {
        $this->template = $template;
        $this->entityManager = $entityManager;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new HtmlResponse($this->template->render(self::TEMPLATE_NAME, ['layout' => 'layout::landing']));
    }
}
