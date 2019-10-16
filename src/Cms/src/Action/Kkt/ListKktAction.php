<?php


namespace Cms\Action\Kkt;

use Doctrine\ORM\EntityManager;
use Office\Entity\Kkt;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class ListKktAction
 *
 * @package Cms\Action\Kkt
 */
class ListKktAction implements ServerMiddlewareInterface
{
    public const TEMPLATE_NAME = 'admin::kkt/list';

    private $template;

    private $entityManager;

    /**
     * ListKktAction constructor.
     *
     * @param EntityManager $entityManager
     * @param TemplateRendererInterface $template
     */
    public function __construct(EntityManager $entityManager, TemplateRendererInterface $template)
    {
        $this->template = $template;
        $this->entityManager = $entityManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $handler): ResponseInterface
    {
        $kkts = $this->entityManager
            ->getRepository(Kkt::class)
            ->createQueryBuilder('k')
            ->where('k.serialNumber IS NOT NULL')
            ->getQuery()
            ->getResult();

        return new HtmlResponse($this->template->render(self::TEMPLATE_NAME, ['kkts' => $kkts]));
    }
}
