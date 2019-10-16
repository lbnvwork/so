<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 09.07.19
 * Time: 14:58
 */

namespace ApiInsales\Handler;

use ApiInsales\Entity\InsalesShop;
use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Office\Entity\Company;
use Office\Entity\Shop;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Template;

/**
 * Class SettingsViewHandler
 * Отображает настройки кассы магазина Insales
 *
 * @package ApiInsales\Handler
 */
class SettingsViewHandler implements MiddlewareInterface
{
    private $template;

    private $entityManager;

    /**
     * SettingsViewHandler constructor.
     *
     * @param Template\TemplateRendererInterface $template
     * @param EntityManager $em
     */
    public function __construct(Template\TemplateRendererInterface $template, EntityManager $em)
    {
        $this->template = $template;
        $this->entityManager = $em;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        /** @var User $user */
        $user = $request->getAttribute(UserInterface::class);

        if (isset($queryParams['user_id'])) {
            /** @var InsalesShop $insalesShop */
            $insalesShop = $this->entityManager->getRepository(InsalesShop::class)
                ->findOneBy(
                    [
                        'userId'        => $queryParams['user_id'],
                        'userSchetmash' => $user,
                    ]
                );
            if ($insalesShop instanceof InsalesShop) {
                /** @var Shop $shop */
                $shop = $insalesShop->getShopSchetmash();
                /** @var Company $company */
                foreach ($user->getCompany() as $company) {
                    $shops = $company->getShop();
                    break;
                }
                return new Response\HtmlResponse(
                    $this->template->render(
                        'insales::settings',
                        [
                            'layout' => 'layout::auth',
                            'shop'   => $shop,
                            'shops'  => $shops,
                        ]
                    )
                );
            }
        }
        return new Response\HtmlResponse(
            $this->template->render(
                'error::400',
                [
                    'layout' => 'layout::auth',
                ]
            ),
            400
        );
    }
}
