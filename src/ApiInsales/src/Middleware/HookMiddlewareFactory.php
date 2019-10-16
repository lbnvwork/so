<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 18.07.19
 * Time: 15:06
 */

namespace ApiInsales\Middleware;

use ApiInsales\Service\WebhookCurlService;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class HookMiddlewareFactory
 *
 * @package ApiInsales\Middleware
 */
class HookMiddlewareFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return HookMiddleware
     */
    public function __invoke(ContainerInterface $container)
    {
        $em = $container->get('doctrine.entity_manager.orm_default');
        /** @var TemplateRendererInterface $template */
        $template = $container->get(TemplateRendererInterface::class);
        $webhookService = $container->get(WebhookCurlService::class);

        return new HookMiddleware($em, $template, $webhookService);
    }
}
