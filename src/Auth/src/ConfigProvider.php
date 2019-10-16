<?php
declare(strict_types=1);

namespace Auth;

/**
 * The configuration provider for the Auth module
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
            ],
            'factories'  => [
                Handler\LoginHandler::class        => Handler\LoginHandlerFactory::class,
                Action\LogoutAction::class         => Action\LogoutActionFactory::class,
                Action\RegisterAction::class       => Action\RegisterActionFactory::class,
                Action\ConfirmAction::class        => Action\ConfirmActionFactory::class,
                Action\ForgetAction::class         => Action\ForgetActionFactory::class,
                Action\RestoreAction::class        => Action\RestoreActionFactory::class,
                Action\ChangePasswordAction::class => Action\ChangePasswordActionFactory::class,
                Action\UserProfileAction::class    => Action\UserProfileActionFactory::class,
                Action\RollbackAction::class       => Action\RollbackActionFactory::class,

                Service\AuthenticationService::class => Service\AuthenticationServiceFactory::class,
                Service\SendMail::class              => Service\SendMailFactory::class,

                UserRepository\Database::class             => UserRepository\DatabaseFactory::class,
                Middleware\AuthenticationMiddleware::class => Middleware\AuthenticationMiddlewareFactory::class,
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
                'auth'   => [__DIR__.'/../templates/auth'],
//                'error' => [__DIR__.'/../templates/error'],
                'layout' => [__DIR__.'/../templates/layout'],
            ],
        ];
    }
}
