<?php

namespace App\Handler;

use Zend\Expressive\Template\TemplateRendererInterface;
use App\Helper\UrlHelper;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

/**
 * Class AppPageFactory
 *
 * @package App\Handler
 */
class AppPageFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return mixed|object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var TemplateRendererInterface $template */
        $template = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;

        $em = $container->get('doctrine.entity_manager.orm_default');

        return new $requestedName($em, $template, $container->get(UrlHelper::class));
    }
}
