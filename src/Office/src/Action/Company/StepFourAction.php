<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 22.03.18
 * Time: 19:56
 */

namespace Office\Action\Company;

use App\Entity\Setting;
use App\Helper\UrlHelper;
use Cms\Service\SettingService;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\Company;
use Office\Entity\Shop;
use Office\Service\SendMail;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

/**
 * Class StepFourAction
 *
 * @package Office\Action\Company
 */
class StepFourAction implements ServerMiddlewareInterface
{
    private $entityManager;

    private $template;

    private $urlHelper;

    private $sendMail;

    /**
     * StepFourAction constructor.
     *
     * @param EntityManager $entityManager
     * @param Template\TemplateRendererInterface $template
     * @param UrlHelper $urlHelper
     * @param SendMail $sendMail
     */
    public function __construct(EntityManager $entityManager, Template\TemplateRendererInterface $template, UrlHelper $urlHelper, SendMail $sendMail)
    {
        $this->entityManager = $entityManager;
        $this->template = $template;
        $this->urlHelper = $urlHelper;
        $this->sendMail = $sendMail;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        /** @var Company $company */
        $company = $request->getAttribute(Company::class);

        $shops = $this->entityManager->getRepository(Shop::class)->findBy(['company' => $company]);
        /** @var Setting[] $settings */
        $settings = $this->entityManager->getRepository(Setting::class)->createQueryBuilder('s', 's.param')
            ->getQuery()->getResult();

        return new HtmlResponse(
            $this->template->render(
                'office::company/step-four',
                [
                    'company'     => $company,
                    'shops'       => $shops,
                    'kktLocation' => $settings[SettingService::KKT_LOCATION],
                ]
            )
        );
    }
}
