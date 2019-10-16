<?php
declare(strict_types=1);

namespace Cms\Action;

use App\Helper\UrlHelper;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class AbstractAction
 *
 * @package Cms\Action
 */
abstract class AbstractAction implements ServerMiddlewareInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var TemplateRendererInterface
     */
    protected $template;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * AbstractAction constructor.
     *
     * @param EntityManager $entityManager
     * @param TemplateRendererInterface $template
     * @param UrlHelper $urlHelper
     */
    public function __construct(EntityManager $entityManager, TemplateRendererInterface $template, UrlHelper $urlHelper)
    {
        $this->entityManager = $entityManager;
        $this->template = $template;
        $this->urlHelper = $urlHelper;
    }
}
