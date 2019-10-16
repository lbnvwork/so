<?php


namespace ApiInsales\Handler;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class ManualHandlerFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return ManualHandler
     */
    public function __invoke(ContainerInterface $container)
    {
        $template = $container->get(TemplateRendererInterface::class);
        return new ManualHandler($template);
    }
}
