<?php
declare(strict_types=1);

namespace Cms\Action;

use App\Helper\UrlHelper;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class AbstractActionFactory
 *
 * @package Cms\Action
 */
class AbstractActionFactory implements FactoryInterface
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
        $template = $container->get(TemplateRendererInterface::class);

        $em = $container->get('doctrine.entity_manager.orm_default');

//        $template->addDefaultParam($requestedName::TEMPLATE_NAME, 'layout', 'layout::cms');

        return new $requestedName($em, $template, $container->get(UrlHelper::class));
    }
}
