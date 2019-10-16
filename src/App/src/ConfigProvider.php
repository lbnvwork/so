<?php

declare(strict_types=1);

namespace App;

use Psr\Log\LoggerInterface;
use Zend\Stratigility\Middleware\ErrorHandler;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 * @codeCoverageIgnore
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
                Service\SpreadsheetCreator\SpreadsheetCreatorService::class => Service\SpreadsheetCreator\SpreadsheetCreatorService::class,
                Handler\PingHandler::class                                  => Handler\PingHandler::class,
            ],
            'factories'  => [
                Handler\LandingApiHandler::class   => Handler\LandingApiFactory::class,
                Handler\HomePageHandler::class     => Handler\HomePageHandlerFactory::class,
                Handler\RetargetPageHandler::class => Handler\AppPageFactory::class,

                Middleware\FlashMessageMiddleware::class => Middleware\FlashMessageFactory::class,

                Helper\UrlHelper::class => Helper\UrlHelperFactory::class,

                Service\SendMail::class         => Service\SendMailFactory::class,
                Service\RecaptchaService::class => Service\RecaptchaServiceFactory::class,
                LoggerInterface::class          => Service\LoggerFactory::class,
            ],
            'delegators' => [
                ErrorHandler::class => [
                    Listener\LoggingErrorListenerDelegatorFactory::class
                ]
            ]
        ];
    }

    /**
     * Returns the templates configuration
     *
     * @return array
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'app'    => [__DIR__.'/../templates/app'],
                'error'  => [__DIR__.'/../templates/error'],
                'layout' => [__DIR__.'/../templates/layout'],
            ],
        ];
    }
}
