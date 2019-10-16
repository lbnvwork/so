<?php


namespace ApiInsales\Service;

use Interop\Container\ContainerInterface;

/**
 * Class WebhookCurlServiceFactory
 *
 * @package ApiInsales\Service
 */
class WebhookCurlServiceFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return WebhookCurlService
     */
    public function __invoke(ContainerInterface $container)
    {
        $insalesSettingsService = $container->get(InsalesSettingsService::class);
        return new WebhookCurlService($insalesSettingsService);
    }
}
