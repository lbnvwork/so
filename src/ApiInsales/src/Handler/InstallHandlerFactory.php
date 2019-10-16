<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 03.07.19
 * Time: 15:23
 */

namespace ApiInsales\Handler;

use ApiInsales\Service\InsalesSettingsService;
use Interop\Container\ContainerInterface;

/**
 * Class InstallHandlerFactory
 *
 * @package ApiInsales\Handler
 */
class InstallHandlerFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return InstallHandler
     */
    public function __invoke(ContainerInterface $container)
    {
        $em = $container->get('doctrine.entity_manager.orm_default');
        $insalesSettingsService = $container->get(InsalesSettingsService::class);

        return new InstallHandler($em, $insalesSettingsService);
    }
}
