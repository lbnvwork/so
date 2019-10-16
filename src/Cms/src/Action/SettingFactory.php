<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 26.01.18
 * Time: 12:00
 */

namespace Cms\Action;

use App\Helper\UrlHelper;
use Cms\Service\SettingService;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class SettingFactory
 * @package Cms\Action
 */
class SettingFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return SettingsAction
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var TemplateRendererInterface $template */
        $template = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;

        $em = $container->get('doctrine.entity_manager.orm_default');

        return new SettingsAction($em, $template, $container->get(SettingService::class), $container->get(UrlHelper::class));
    }
}
