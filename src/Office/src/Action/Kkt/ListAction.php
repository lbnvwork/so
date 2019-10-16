<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 22.03.18
 * Time: 19:56
 */

namespace Office\Action\Kkt;

use App\Entity\Setting;
use App\Helper\UrlHelper;
use Auth\Entity\User;
use Cms\Service\SettingService;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Template;

/**
 * Class ListAction
 *
 * @package Office\Action\Company
 */
class ListAction implements ServerMiddlewareInterface
{
    private $entityManager;

    private $template;

    private $urlHelper;

    /**
     * ListAction constructor.
     *
     * @param EntityManager $entityManager
     * @param Template\TemplateRendererInterface $template
     * @param UrlHelper $urlHelper
     */
    public function __construct(EntityManager $entityManager, Template\TemplateRendererInterface $template, UrlHelper $urlHelper)
    {
        $this->entityManager = $entityManager;
        $this->template = $template;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return Response|HtmlResponse|static
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute(UserInterface::class);

        /** @var Setting[] $settings */
        $settings = $this->entityManager->getRepository(Setting::class)->createQueryBuilder('s', 's.param')->getQuery()->getResult();


        return new HtmlResponse(
            $this->template->render(
                'office::kkt/list',
                [
                    'user'        => $user,
                    'kktLocation' => $settings[SettingService::KKT_LOCATION],
                ]
            )
        );
    }
}
