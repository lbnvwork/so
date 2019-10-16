<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 12.08.19
 * Time: 14:13
 */

namespace ApiInsales\Handler;

use ApiInsales\Service\InsalesSettingsService;
use ApiInsales\Service\LoginService;
use App\Helper\UrlHelper;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class AutologinHandlerFactory
 *
 * @package ApiInsales\Handler
 */
class AutologinHandlerFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return AutologinHandler
     */
    public function __invoke(ContainerInterface $container)
    {
        $em = $container->get('doctrine.entity_manager.orm_default');
        $insalesSettingsService = $container->get(InsalesSettingsService::class);
        $urlHelper = $container->get(UrlHelper::class);
        /** @var TemplateRendererInterface $template */
        $template = $container->get(TemplateRendererInterface::class);
        $loginService = $container->get(LoginService::class);

        return new AutologinHandler($em, $insalesSettingsService, $urlHelper, $template, $loginService);
    }
}
