<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\RecaptchaService;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class HomePageHandlerFactory
 *
 * @package App\Handler
 */
class HomePageHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $template = $container->has(TemplateRendererInterface::class)
            ? $container->get(TemplateRendererInterface::class)
            : null;
        $recaptchaService = $container->get(RecaptchaService::class);
        $em = $container->get('doctrine.entity_manager.orm_default');

        return new HomePageHandler($em, $recaptchaService, $template);
    }
}
