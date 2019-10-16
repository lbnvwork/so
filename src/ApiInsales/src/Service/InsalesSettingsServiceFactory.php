<?php


namespace ApiInsales\Service;

use App\Helper\UrlHelper;
use Interop\Container\ContainerInterface;

/**
 * Class InsalesSettingsServiceFactory
 *
 * @package ApiInsales\Service
 */
class InsalesSettingsServiceFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return InsalesSettingsService
     */
    public function __invoke(ContainerInterface $container)
    {
        $urlHelper = $container->get(UrlHelper::class);
        $config = $container->get('config');
        return new InsalesSettingsService($urlHelper, $config);
    }
}
