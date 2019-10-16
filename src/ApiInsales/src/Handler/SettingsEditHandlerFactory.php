<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 10.07.19
 * Time: 13:26
 */

namespace ApiInsales\Handler;

use App\Helper\UrlHelper;
use Interop\Container\ContainerInterface;

class SettingsEditHandlerFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return SettingsEditHandler
     */
    public function __invoke(ContainerInterface $container)
    {
        $em = $container->get('doctrine.entity_manager.orm_default');

        return new SettingsEditHandler($em, $container->get(UrlHelper::class));
    }
}
