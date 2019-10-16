<?php

use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Expressive\ZendView\HelperPluginManagerFactory;
use Zend\Expressive\ZendView\ZendViewRendererFactory;
use Zend\View\HelperPluginManager;

return [
    'dependencies' => [
        'factories' => [
            TemplateRendererInterface::class => ZendViewRendererFactory::class,
            HelperPluginManager::class => HelperPluginManagerFactory::class,
        ],
    ],

    'templates' => [
        'layout' => 'layout::default',
    ],

    'view_helpers' => [
        // zend-servicemanager-style configuration for adding view helpers:
        // - 'aliases'
        // - 'invokables'
        'factories' => [
            \App\Helper\Paginator::class            => \App\Helper\WidgetFactory::class,
        ],
        'aliases'   => [
            'paginator'                            => \App\Helper\Paginator::class,
        ]
        // - 'abstract_factories'
        // - etc.
    ],
];
