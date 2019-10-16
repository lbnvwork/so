<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 09.02.18
 * Time: 14:24
 */
return [
    'dependencies' => [
        'invokables' => [
        ],

        'factories' => [
            App\Command\InitAppCommand::class              => App\Command\InitAppCommandFactory::class,
            Cms\Command\GetAllFnCommand::class             => Cms\Command\GetAllFnCommandFactory::class,
            Office\Command\CheckFiscalizeCommand::class    => Office\Command\UmkaCommandFactory::class,
            Office\Command\FiscalizeCommand::class         => Office\Command\UmkaCommandFactory::class,
            Office\Command\CheckUseKktCommand::class       => Office\Command\ServiceCommandFactory::class,
            Office\Command\CheckUserPaymentsCommand::class => Office\Command\ServiceCommandFactory::class,
            Office\Command\PaymentKktCommand::class        => Office\Command\ServiceCommandFactory::class,
            Office\Command\DeleteCompanyCommand::class     => ApiV1\Command\ApiCommandFactory::class,
            Office\Command\UpdateProcessingCommand::class  => ApiV1\Command\ApiCommandFactory::class,
            Office\Command\SearchDuplicateCommand::class   => Office\Command\UmkaCommandFactory::class,
            ApiV1\Command\SendCheckCommand::class          => ApiV1\Command\SendCheckCommandFactory::class,
            ApiV1\Command\SendCheckPackCommand::class      => ApiV1\Command\SendCheckPackCommandFactory::class,
            ApiV1\Command\GetCheckPackCommand::class       => ApiV1\Command\GetCheckPackCommandFactory::class,
            ApiV1\Command\SendCallbackCommand::class       => ApiV1\Command\SendCallbackCommandFactory::class,
            ApiV1\Command\CheckStatusCommand::class        => ApiV1\Command\ApiCommandFactory::class,
            ApiV1\Command\CloseFnCommand::class            => ApiV1\Command\ApiCommandFactory::class,
            ApiV1\Command\CloseShiftCommand::class         => ApiV1\Command\ApiCommandFactory::class,
            ApiV1\Command\OpenShiftCommand::class          => ApiV1\Command\ApiCommandFactory::class,
            ApiV1\Command\GetLogsCommand::class            => ApiV1\Command\ApiCommandFactory::class,
        ],
    ],

    'console' => [
        'commands' => [
            App\Command\InitAppCommand::class,
            Cms\Command\GetAllFnCommand::class,
            Office\Command\CheckFiscalizeCommand::class,
            Office\Command\FiscalizeCommand::class,
            Office\Command\CheckUseKktCommand::class,
            Office\Command\CheckUserPaymentsCommand::class,
            Office\Command\DeleteCompanyCommand::class,
            Office\Command\PaymentKktCommand::class,
            Office\Command\UpdateProcessingCommand::class,
            Office\Command\SearchDuplicateCommand::class,
            ApiV1\Command\CheckStatusCommand::class,
            ApiV1\Command\SendCheckCommand::class,
            ApiV1\Command\SendCheckPackCommand::class,
            ApiV1\Command\GetCheckPackCommand::class,
            ApiV1\Command\SendCallbackCommand::class,
            ApiV1\Command\CloseFnCommand::class,
            ApiV1\Command\CloseShiftCommand::class,
            ApiV1\Command\OpenShiftCommand::class,
            ApiV1\Command\GetLogsCommand::class,
        ],
    ],
];