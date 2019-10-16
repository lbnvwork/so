<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 09.07.19
 * Time: 14:58
 */

namespace ApiInsales\Handler;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class SettingsViewHandlerFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return SettingsViewHandler
     */
    public function __invoke(ContainerInterface $container)
    {
        $template = $container->get(TemplateRendererInterface::class);
        $em = $container->get('doctrine.entity_manager.orm_default');
        return new SettingsViewHandler($template, $em);
    }
}
