<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 25.01.18
 * Time: 14:07
 */

namespace Office\Action\Check;

use App\Helper\UrlHelper;
use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\Processing;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class ViewAction
 *
 * @package Office\Action\Check
 */
class ViewAction implements ServerMiddlewareInterface
{
    public const TEMPLATE_NAME = 'admin::processing/view';

    private $template;

    private $entityManager;

    private $urlHelper;

    /**
     * ProcessingAction constructor.
     *
     * @param EntityManager $entityManager
     * @param TemplateRendererInterface $template
     * @param UrlHelper $urlHelper
     */
    public function __construct(EntityManager $entityManager, TemplateRendererInterface $template, UrlHelper $urlHelper)
    {
        $this->template = $template;
        $this->entityManager = $entityManager;
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
        $id = $request->getAttribute('id');
        /** @var User $user */
        $user = $request->getAttribute(UserInterface::class);
        /** @var Processing $item */
        $item = $this->entityManager->getRepository(Processing::class)->findOneBy(['id' => $id]);
        $company = $item->getShop()->getCompany();

        $allow = false;
        foreach ($user->getCompany() as $c) {
            if ($c === $company) {
                $allow = true;
            }
        }
        if (!$allow) {
            return (new Response())->withStatus(403);
        }

        return new HtmlResponse(
            $this->template->render(
                self::TEMPLATE_NAME,
                [
                    'item' => $item,
                ]
            )
        );
    }
}
