<?php

namespace Office\Action\Kkt;

use App\Helper\UrlHelper;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class FilesActionFactory
 *
 * @package Office\Action
 */
class TariffActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $template = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;

        return new TariffAction($container->get('doctrine.entity_manager.orm_default'), $container->get(UrlHelper::class), $template);
    }
}
