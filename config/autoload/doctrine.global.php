<?php
return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'params' => [
                    'charset'       => 'utf8',
                    'driverOptions' => [
                        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                    ],
                ],
            ],
        ],
        'driver'     => [
            'orm_default' => [
                'class'   => \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain::class,
                'drivers' => [
                    'App\Entity'        => 'my_entity',
                    'Auth\Entity'       => 'my_entity',
                    'Permission\Entity' => 'my_entity',
                    'Office\Entity'     => 'my_entity',
                    'ApiInsales\Entity' => 'my_entity',
                ],
            ],
            'my_entity'   => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__.'/../../src/App/src/Entity',
                    __DIR__.'/../../src/Auth/src/Entity',
                    __DIR__.'/../../src/Permission/src/Entity',
                    __DIR__.'/../../src/Office/src/Entity',
                    __DIR__.'/../../src/ApiInsales/src/Entity',
                ],
            ],
        ],
    ],
];
