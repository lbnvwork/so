<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 26.03.18
 * Time: 21:28
 */

namespace Office\Action\Billing;

use App\Helper\UrlHelper;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class InvoiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $template = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;

        return new $requestedName($container->get('doctrine.entity_manager.orm_default'), $template, $container->get(UrlHelper::class));
    }
}
