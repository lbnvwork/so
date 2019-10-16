<?php
declare(strict_types=1);

namespace Permission;

/**
 * The configuration provider for the Permission module
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
            'view_helpers' => $this->getViewHelpers(),
            'rbac'         => include __DIR__.'/../config/rbac.php',
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
                Middleware\PermissionMiddleware::class => Middleware\PermissionMiddlewareFactory::class,
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
                'app'    => [__DIR__.'/../templates/app'],
                'error'  => [__DIR__.'/../templates/error'],
                'layout' => [__DIR__.'/../templates/layout'],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getViewHelpers(): array
    {
        return [
            'factories' => [
                Helper\IsGranted::class => Helper\IsGrantedFactory::class,
            ],
            'aliases'   => [
                'isGranted' => Helper\IsGranted::class,
            ],
        ];
    }
}
