<?php

namespace Office\Action\Kkt;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use App\Service\SpreadsheetCreator\SpreadsheetCreatorService;

/**
 * Class StatementActionFactory
 *
 * @package Office\Action\Kkt
 */
class StatementActionFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return StatementAction
     */
    public function __invoke(ContainerInterface $container)
    {
        $router = $container->get(RouterInterface::class);
        $template = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;
        $em = $container->get('doctrine.entity_manager.orm_default');
        $recaptchaService = $container->get(SpreadsheetCreatorService::class);

        return new StatementAction($em, $router, $template, $recaptchaService);
    }
}
