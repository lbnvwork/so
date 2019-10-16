<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 24.01.18
 * Time: 14:14
 */

namespace Cms\Action\Invoice;

use App\Helper\UrlHelper;
use Interop\Container\ContainerInterface;
use Office\Service\SendMail;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class IndexActionFactory
 *
 * @package Cms\Action\Invoice
 */
class IndexActionFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return mixed|object
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var TemplateRendererInterface $template */
        $template = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;

        $em = $container->get('doctrine.entity_manager.orm_default');
//        $template->addDefaultParam($requestedName::TEMPLATE_NAME, 'layout', 'layout::cms');

        return new $requestedName($em, $template, $container->get(UrlHelper::class), $container->get(SendMail::class));
    }
}
