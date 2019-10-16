<?php
declare(strict_types=1);

namespace Cms;

/**
 * The configuration provider for the Cms module
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
    public function getDependencies(): array
    {
        return [
            'invokables' => [
            ],
            'factories'  => [
                Action\IndexPageAction::class => Action\AbstractActionFactory::class,
                Action\UsersAction::class     => Action\AbstractActionFactory::class,
                Action\UserAction::class      => Action\AbstractActionFactory::class,
                Action\RfnAction::class       => Action\AbstractActionFactory::class,
                Action\RolesAction::class     => Action\AbstractActionFactory::class,
                Action\RoleAction::class      => Action\AbstractActionFactory::class,
                Action\WidgetsAction::class   => Action\AbstractActionFactory::class,

                Action\Service\ListAction::class => Action\AbstractActionFactory::class,
                Action\Service\EditAction::class => Action\AbstractActionFactory::class,
                Action\Tariff\ListAction::class  => Action\AbstractActionFactory::class,
                Action\Tariff\EditAction::class  => Action\AbstractActionFactory::class,
                Action\SettingsAction::class     => Action\SettingFactory::class,

                Action\ReferralAdminAction::class => Action\AbstractActionFactory::class,

                Action\Kkt\DeletedAction::class    => Action\AbstractActionFactory::class,
                Action\Kkt\EditAction::class       => Action\AbstractActionFactory::class,
                Action\Kkt\ListKktAction::class    => Action\AbstractActionFactory::class,
                Action\Kkt\CloseShiftAction::class => Action\Kkt\CloseActionFactory::class,
                Action\Kkt\CloseFnAction::class    => Action\Kkt\CloseActionFactory::class,
                Action\Kkt\KktInfoAction::class    => Action\Kkt\KktInfoActionFactory::class,

                Action\Invoice\IndexAction::class    => Action\Invoice\IndexActionFactory::class,
                Action\Invoice\EditAction::class     => Action\AbstractActionFactory::class,
                Action\Invoice\EditItemAction::class => Action\AbstractActionFactory::class,
                Action\Invoice\DownloadAction::class => Action\AbstractActionFactory::class,

                Action\MoneyHistory\ListAction::class => Action\AbstractActionFactory::class,
                Action\MoneyHistory\EditAction::class => Action\AbstractActionFactory::class,

                Action\Company\ListAction::class   => Action\AbstractActionFactory::class,
                Action\Company\EditAction::class   => Action\AbstractActionFactory::class,
                Action\Company\DeleteAction::class => Action\AbstractActionFactory::class,

                Action\File\EditAction::class => Action\AbstractActionFactory::class,
                Action\File\ListAction::class => Action\AbstractActionFactory::class,

                Action\Ofd\ListAction::class => Action\AbstractActionFactory::class,
                Action\Ofd\EditAction::class => Action\AbstractActionFactory::class,

                Action\ApiTokenAction::class          => Action\AbstractActionFactory::class,
                Action\Processing\ListAction::class   => Action\AbstractActionFactory::class,
                Action\Processing\ViewAction::class   => Action\AbstractActionFactory::class,
                Action\Processing\EditAction::class   => Action\AbstractActionFactory::class,
                Action\Processing\DeleteAction::class => Action\AbstractActionFactory::class,

                Middleware\ProcessingMiddleware::class => Middleware\ProcessingMiddlewareFactory::class,

                Service\SettingService::class => Service\SettingFactory::class,
            ],
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
                'admin' => [__DIR__.'/../templates/admin'],
                'error' => [__DIR__.'/../templates/error'],
            ],
        ];
    }
}
