<?php

namespace Office\Action;

use App\Helper\UrlHelper;
use Office\Service\SendMail;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class CompanyEditActionFactory
{
    public function __invoke(ContainerInterface $container, $request)
    {
        $template = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;
        return new $request($container->get('doctrine.entity_manager.orm_default'), $template, $container->get(UrlHelper::class), $container->get(SendMail::class));
    }
}
