<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 26.01.18
 * Time: 11:58
 */

namespace Cms\Service;

use Interop\Container\ContainerInterface;

/**
 * Class SiteFactory
 *
 * @package Cms\Service
 */
class SettingFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return SettingService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container)
    {
        $em = $container->get('doctrine.entity_manager.orm_default');

        return new SettingService($em);
    }
}
