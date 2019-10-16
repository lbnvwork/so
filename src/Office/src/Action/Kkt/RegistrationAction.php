<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 14.01.19
 * Time: 12:42
 */

namespace Office\Action\Kkt;

use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use Doctrine\ORM\EntityManager;
use App\Helper\UrlHelper;

/**
 * Class RegistrationAction
 *
 * @package Office\Action\Kkt
 */
class RegistrationAction implements ServerMiddlewareInterface
{
    private $urlHelper;

    private $template;

    private $entityManager;

    /**
     * RegistrationAction constructor.
     *
     * @param EntityManager $entityManager
     * @param Template\TemplateRendererInterface $template
     * @param UrlHelper $urlHelper
     */
    public function __construct(EntityManager $entityManager, Template\TemplateRendererInterface $template, UrlHelper $urlHelper
    )
    {
        $this->entityManager = $entityManager;
        $this->template = $template;
        $this->urlHelper = $urlHelper;
    }
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|HtmlResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        return new HtmlResponse($this->template->render(
            'office::kkt/registration',
            [
                'id'=>$request->getAttribute('id')
            ]
        ));
    }
}
