<?php

namespace Office;

/**
 * The configuration provider for the office module
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
            'invokables' => [
            ],
            'factories'  => [
                Action\HomePageAction::class          => Action\HomePageFactory::class,
                Action\OfertaAction::class            => Action\HomePageFactory::class,
                Action\CompanyAction::class           => Action\CompanyActionFactory::class,
                Action\CompanyEditAction::class       => Action\CompanyEditActionFactory::class,
                Action\Company\StepOneAction::class   => Action\CompanyEditActionFactory::class,
                Action\Company\StepTwoAction::class   => Action\CompanyEditActionFactory::class,
                Action\Company\StepThreeAction::class => Action\CompanyEditActionFactory::class,
                Action\Company\StepFourAction::class  => Action\CompanyEditActionFactory::class,

                Action\Billing\IndexAction::class       => Action\Billing\InvoiceFactory::class,
                Action\Billing\InvoiceAction::class     => Action\Billing\InvoiceFactory::class,
                Action\Billing\ReferralAction::class    => Action\Billing\InvoiceFactory::class,
                Action\Billing\ReferralPayAction::class => Action\Billing\InvoiceFactory::class,

                Action\Check\ListAction::class => Action\Billing\InvoiceFactory::class,
                Action\Check\ViewAction::class => Action\Billing\InvoiceFactory::class,

                Action\Kkt\ListAction::class         => Action\CompanyEditActionFactory::class,
                Action\Kkt\FilesAction::class        => Action\Kkt\FilesActionFactory::class,
                Action\Kkt\TariffAction::class       => Action\Kkt\TariffActionFactory::class,
                Action\Kkt\StatementAction::class    => Action\Kkt\StatementActionFactory::class,
                Action\Kkt\RegistrationAction::class => Action\CompanyEditActionFactory::class,

                Action\Api\GetKktAction::class     => Action\Api\ApiFactory::class,
                Action\Api\SetRnmAction::class     => Action\Api\ApiFactory::class,
                Action\Api\GetAccessAction::class  => Action\Api\ApiFactory::class,
                Action\Api\GetkktInfoAction::class => Action\Api\GetKktInfoActionFactory::class,

                Middleware\CheckProfileMiddleware::class => Middleware\CheckProfileMiddlewareFactory::class,
                Middleware\TariffCookieMiddleware::class => Middleware\TariffCookieMiddlewareFactory::class,
                Middleware\CheckUserKktMiddleware::class => Middleware\CheckUserKktMiddlewareFactory::class,
                Middleware\CheckCompanyMiddleware::class => Middleware\CheckCompanyMiddlewareFactory::class,

                Service\SendMail::class   => Service\SendMailFactory::class,
                Service\Umka::class       => Service\UmkaFactory::class,
                Service\KktService::class => Service\KktServiceFactory::class,
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
                'office' => [__DIR__.'/../templates/office'],
                'pdf'    => [__DIR__.'/../templates/pdf'],
                'error'  => [__DIR__.'/../templates/error'],
                'layout' => [__DIR__.'/../templates/layout'],
            ],
        ];
    }
}
