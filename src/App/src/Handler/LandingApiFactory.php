<?php

namespace App\Handler;

use App\Helper\UrlHelper;
use App\Service\SendMail;
use Psr\Container\ContainerInterface;
use App\Service\RecaptchaService;

/**
 * Class LandingApiFactory
 *
 * @package App\Handler
 */
class LandingApiFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return LandingApiHandler
     */
    public function __invoke(ContainerInterface $container)
    {
        return new LandingApiHandler(
            $container->get(SendMail::class),
            $container->get('doctrine.entity_manager.orm_default'),
            $container->get(UrlHelper::class),
            $container->get(RecaptchaService::class)
        );
    }
}
