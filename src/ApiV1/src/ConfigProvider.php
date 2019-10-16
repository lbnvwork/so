<?php

namespace ApiV1;

/**
 * The configuration provider for the ApiV1 module
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
                Action\TokenAction::class         => Action\TokenActionFactory::class,
                Action\RegisterCheckAction::class => Action\RegisterCheckActionFactory::class,
                Action\ReportAction::class        => Action\RegisterCheckActionFactory::class,

                Middleware\CheckRequestMiddleware::class        => Action\ApiFactory::class,
                Middleware\ProcessingMiddleware::class          => Middleware\ProcessingMiddlewareFactory::class,
                Middleware\LoggerMiddlewareFactory::API_V1_NAME => Middleware\LoggerMiddlewareFactory::class,

                Service\Umka\UmkaApi::class      => Service\Umka\UmkaApiFactory::class,
                Service\TokenService::class      => Service\TokenServiceFactory::class,
                Service\Check\Normal::class      => Service\Check\CheckFactory::class,
                Service\Check\Correction::class  => Service\Check\CheckFactory::class,
                Service\Check\Normal\Pack::class => Service\Check\Normal\PackFactory::class,
                Service\Umka\UmkaLkApi::class    => \Office\Service\UmkaFactory::class,
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
