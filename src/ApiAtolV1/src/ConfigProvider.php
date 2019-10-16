<?php

namespace ApiAtolV1;

use ApiV1\Action\TokenActionFactory;
use ApiV1\Middleware\LoggerMiddlewareFactory;

/**
 * The configuration provider for the ApiAtolV1 module
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
            'factories' => [
                Action\TokenAction::class         => TokenActionFactory::class,
                Action\RegisterCheckAction::class => Action\ApiFactory::class,
                Action\ReportAction::class        => Action\ApiFactory::class,

                Middleware\CheckRequestMiddleware::class  => Action\ApiFactory::class,
                LoggerMiddlewareFactory::API_ATOL_V1_NAME => LoggerMiddlewareFactory::class,
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
                'app'    => [__DIR__.'/../templates/app'],
                'error'  => [__DIR__.'/../templates/error'],
                'layout' => [__DIR__.'/../templates/layout'],
            ],
        ];
    }
}
