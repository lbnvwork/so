<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 03.07.19
 * Time: 15:25
 */

namespace ApiInsales;

class ConfigProvider
{
    public function __invoke()
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
    public function getDependencies()
    {
        return [
            'invokables' => [
                Service\WebhookParserService::class => Service\WebhookParserService::class,
            ],
            'factories'  => [
                Handler\InstallHandler::class      => Handler\InstallHandlerFactory::class,
                Handler\SettingsViewHandler::class => Handler\SettingsViewHandlerFactory::class,
                Handler\SettingsEditHandler::class => Handler\SettingsEditHandlerFactory::class,
                Handler\LoginHandler::class        => Handler\LoginHandlerFactory::class,
                Handler\AutologinHandler::class    => Handler\AutologinHandlerFactory::class,
                Handler\PrintCheckHandler::class   => Handler\PrintCheckHandlerFactory::class,
                Handler\UninstallHandler::class    => Handler\UninstallHandlerFactory::class,
                Handler\ManualHandler::class       => Handler\ManualHandlerFactory::class,

                Service\WebhookCurlService::class     => Service\WebhookCurlServiceFactory::class,
                Service\InsalesSettingsService::class => Service\InsalesSettingsServiceFactory::class,
                Service\LoginService::class           => Service\LoginServiceFactory::class,

                Middleware\HookMiddleware::class => Middleware\HookMiddlewareFactory::class,
            ],
        ];
    }

    /**
     * Returns the templates configuration
     *
     * @return array
     */
    public function getTemplates()
    {
        return [
            'paths' => [
                'error'   => [__DIR__.'/../../App/templates/error'],
                'layout'  => [__DIR__.'/../../Auth/templates/layout'],
                'insales' => [__DIR__.'/../templates/insales'],
            ],
        ];
    }
}
