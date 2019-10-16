<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareFactory;

/**
 * Setup routes with a single request method:
 * $app->get('/', App\Handler\HomePageHandler::class, 'home');
 * $app->post('/album', App\Handler\AlbumCreateHandler::class, 'album.create');
 * $app->put('/album/:id', App\Handler\AlbumUpdateHandler::class, 'album.put');
 * $app->patch('/album/:id', App\Handler\AlbumUpdateHandler::class, 'album.patch');
 * $app->delete('/album/:id', App\Handler\AlbumDeleteHandler::class, 'album.delete');
 * Or with multiple request methods:
 * $app->route('/contact', App\Handler\ContactHandler::class, ['GET', 'POST', ...], 'contact');
 * Or handling all request methods:
 * $app->route('/contact', App\Handler\ContactHandler::class)->setName('contact');
 * or:
 * $app->route(
 *     '/contact',
 *     App\Handler\ContactHandler::class,
 *     Zend\Expressive\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 */
return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
//    $app->get('/', App\Handler\HomePageHandler::class, 'landing');
//    $app->get('/api/ping', App\Handler\PingHandler::class, 'api.ping');
    $getPostMethods = [
        'GET',
        'POST',
    ];

    /** @var \Zend\Expressive\Application $app */

    $app->get('/', App\Handler\HomePageHandler::class, 'landing');

    $app->get('/retarget', App\Handler\RetargetPageHandler::class, 'retarget');

    $app->get(
        '/lk', [
        \Office\Middleware\CheckProfileMiddleware::class,
        \Office\Middleware\TariffCookieMiddleware::class,
        \Cms\Action\WidgetsAction::class,
    ], 'home'
    );
    $app->post(
        '/api/landing/{action:\w+}', [
        App\Handler\LandingApiHandler::class,
        \Auth\Action\RegisterAction::class,
    ], 'api.landing'
    );
//$app->get('/lk/api/ping', App\Action\PingAction::class, 'api.ping');
//auth
    $app->route(
        '/lk/user/login', Auth\Handler\LoginHandler::class, $getPostMethods, 'login'
    );
    $app->route(
        '/lk/user/register', Auth\Action\RegisterAction::class, $getPostMethods, 'register'
    );
    $app->route(
        '/lk/user/change-password', Auth\Action\ChangePasswordAction::class, $getPostMethods, 'user.changePassword'
    );
    $app->route(
        '/lk/user/profile', [
        \Office\Middleware\TariffCookieMiddleware::class,
        Auth\Action\UserProfileAction::class,
    ], $getPostMethods, 'user.profile'
    );
    $app->get('/lk/user/rollback', \Auth\Action\RollbackAction::class, 'user.rollback');
    $app->get('/lk/user/logout', Auth\Action\LogoutAction::class, 'logout');
    $app->get('/lk/user/confirm/{hash:\w+}', Auth\Action\ConfirmAction::class, 'user.confirm');
    $app->get('/lk/user/restore/{hash:\w+}', Auth\Action\RestoreAction::class, 'user.restore');
    $app->route(
        '/lk/user/forget', Auth\Action\ForgetAction::class, $getPostMethods, 'user.forget'
    );

    $app->get('/lk/oferta', \Office\Action\OfertaAction::class, 'office.oferta');
    $app->get(
        '/lk/company', [
        \Office\Middleware\CheckProfileMiddleware::class,
        \Office\Middleware\TariffCookieMiddleware::class,
        \Office\Action\CompanyAction::class,
    ], 'office.company'
    );
    $app->route(
        '/lk/company/{id:\d+}', [
        \Office\Middleware\CheckProfileMiddleware::class,
        \Office\Action\CompanyEditAction::class,
    ], $getPostMethods, 'office.company.edit'
    );
    $app->route(
        '/lk/company/{id:\d+}/step-one', [
        \Office\Middleware\CheckProfileMiddleware::class,
        \Office\Middleware\TariffCookieMiddleware::class,
        \Office\Action\Company\StepOneAction::class,
    ], $getPostMethods, 'office.company.stepOne'
    );
    $app->route(
        '/lk/company/{id:\d+}/step-two', [
        \Office\Middleware\CheckProfileMiddleware::class,
        \Office\Middleware\CheckCompanyMiddleware::class,
        \Office\Action\Company\StepTwoAction::class,
    ], $getPostMethods, 'office.company.stepTwo'
    );
    $app->route(
        '/lk/company/{id:\d+}/step-three[/edit-shop/{shopId:\d+}]',
        [
            \Office\Middleware\CheckProfileMiddleware::class,
            \Office\Middleware\CheckCompanyMiddleware::class,
            \Office\Action\Company\StepThreeAction::class,
        ],
        $getPostMethods,
        'office.company.stepThree'
    );
    $app->route(
        '/lk/company/{id:\d+}/step-four', [
        \Office\Middleware\CheckProfileMiddleware::class,
        \Office\Middleware\CheckCompanyMiddleware::class,
        \Office\Action\Company\StepFourAction::class,
    ], $getPostMethods, 'office.company.stepFour'
    );
    $app->route(
        '/lk/kkt/list', [
        \Office\Middleware\CheckProfileMiddleware::class,
        \Office\Action\Kkt\ListAction::class,
    ], $getPostMethods, 'office.kkt.list'
    );
    $app->route(
        '/lk/kkt/{id:\d+}/files', [
        \Office\Middleware\CheckProfileMiddleware::class,
        \Office\Middleware\CheckUserKktMiddleware::class,
        \Office\Action\Kkt\FilesAction::class,
    ], $getPostMethods, 'office.kkt.files'
    );
    $app->route(
        '/lk/kkt/{id:\d+}/tariff', [
        \Office\Middleware\CheckProfileMiddleware::class,
        \Office\Middleware\CheckUserKktMiddleware::class,
        \Office\Action\Kkt\TariffAction::class,
    ], $getPostMethods, 'office.kkt.kkt-tariff'
    );
    $app->get(
        '/lk/kkt/{id:\d+}/statement', [
        \Office\Middleware\CheckProfileMiddleware::class,
        \Office\Action\Kkt\StatementAction::class,
    ], 'office.kkt.statement'
    );
    $app->get(
        '/lk/kkt/{id:\d+}/registration', [
        \Office\Middleware\CheckProfileMiddleware::class,
        \Office\Action\Kkt\RegistrationAction::class,
    ], 'office.kkt.registration'
    );
    $app->get(
        '/lk/check/index', [
        \Office\Middleware\CheckProfileMiddleware::class,
        \Office\Action\Check\ListAction::class,
    ], 'office.check'
    );
    $app->get(
        '/lk/check/view/{id:\d+}', [
        \Office\Middleware\CheckProfileMiddleware::class,
        \Office\Action\Check\ViewAction::class,
    ], 'office.check.view'
    );

    $app->get(
        '/lk/billing/index', [
        \Office\Middleware\CheckProfileMiddleware::class,
        \Office\Action\Billing\IndexAction::class,
    ], 'office.billing'
    );
    $app->get(
        '/lk/billing/invoice[/{id:\d+}/pdf]', [
        \Office\Middleware\CheckProfileMiddleware::class,
        \Office\Action\Billing\InvoiceAction::class,
    ], 'office.invoice'
    );
    $app->get(
        '/lk/billing/referral', [
        \Office\Middleware\CheckProfileMiddleware::class,
        \Office\Action\Billing\ReferralAction::class,
    ], 'office.referral'
    );
    $app->get(
        '/lk/billing/referralpay', [
        \Office\Middleware\CheckProfileMiddleware::class,
        \Office\Action\Billing\ReferralPayAction::class,
    ], 'office.referralpay'
    );

    $app->route(
        '/lk/api/kkt/{id:\d+}',
        [
            \Office\Middleware\CheckUserKktMiddleware::class,
            \Office\Action\Api\GetKktAction::class,
        ],
        [
            'POST',
            'DELETE',
        ], 'office.kkt.get'
    );
    $app->post(
        '/lk/api/kkt/{id:\d+}/rnm',
        [
            \Office\Middleware\CheckUserKktMiddleware::class,
            \Office\Action\Api\SetRnmAction::class,
        ],
        'office.kkt.rnm'
    );
    $app->get(
        '/lk/api/kkt/{id:\d+}/info',
        [
            \Office\Middleware\CheckUserKktMiddleware::class,
            \Office\Action\Api\GetkktInfoAction::class,
        ],
        'office.kkt.info'
    );
    $app->get('/lk/api/access', \Office\Action\Api\GetAccessAction::class, 'office.access');

//admin
    $app->get('/lk/admin', \Cms\Action\IndexPageAction::class, 'admin.index');
//admin super
    $app->get('/lk/admin/users', \Cms\Action\UsersAction::class, 'admin.users');
    $app->route(
        '/lk/admin/user/{id:\d+}[/{action:\w+}]', \Cms\Action\UserAction::class, $getPostMethods, 'admin.user'
    );
    $app->get('/lk/admin/reestrfn', \Cms\Action\RfnAction::class, 'admin.rfn');
    $app->get('/lk/admin/roles', \Cms\Action\RolesAction::class, 'admin.roles');
    $app->route(
        '/lk/admin/role/{id:\d+}', \Cms\Action\RoleAction::class, $getPostMethods, 'admin.role'
    );
    $app->route(
        '/lk/admin/settings', \Cms\Action\SettingsAction::class, $getPostMethods, 'admin.settings'
    );
    $app->get('/lk/admin/invoice[/{id:\d+}/confirm]', \Cms\Action\Invoice\IndexAction::class, 'admin.invoice');
    $app->get('/lk/admin/invoice/{id:\d+}/download', \Cms\Action\Invoice\DownloadAction::class, 'admin.invoice.download');
    $app->route(
        '/lk/admin/invoice/{id:\d+}/edit[/{itemId:\d+}[/{itemAction:\w+}]]', [
        \Cms\Action\Invoice\EditAction::class,
        \Cms\Action\Invoice\EditItemAction::class,
    ], [
        'GET',
        'POST',
        'DELETE',
    ], 'admin.invoice.edit'
    );
    $app->get('/lk/admin/ofd', \Cms\Action\Ofd\ListAction::class, 'admin.ofd');

//$app->get(
    $app->route(
        '/lk/admin/referral', \Cms\Action\ReferralAdminAction::class, $getPostMethods, 'admin.referral'
    );

    $app->get('/lk/admin/api-token', \Cms\Action\ApiTokenAction::class, 'admin.apiKey');
    $app->get('/lk/admin/processing', \Cms\Action\Processing\ListAction::class, 'admin.processing');
    $app->get('/lk/admin/processing/{id:\d+}', \Cms\Action\Processing\ViewAction::class, 'admin.processing.view');
    $app->route(
        '/lk/admin/processing/{id:\d+}/edit',
        [
            \Cms\Middleware\ProcessingMiddleware::class,
            \Cms\Action\Processing\EditAction::class,
        ],
        $getPostMethods, 'admin.processing.edit'
    );
    $app->get(
        '/lk/admin/processing/{id:\d+}/rm',
        [
            \Cms\Middleware\ProcessingMiddleware::class,
            \Cms\Action\Processing\DeleteAction::class,
        ],
        'admin.processing.rm'
    );

    $app->route(
        '/lk/admin/ofd/{id:\d+}', \Cms\Action\Ofd\EditAction::class, [
        'GET',
        'POST',
        'DELETE',
    ], 'admin.ofd.edit'
    );
    $app->get('/lk/admin/kkt', \Cms\Action\Kkt\ListKktAction::class, 'admin.kkt');
    $app->route(
        '/lk/admin/kkt/{id:\d+}',

        \Cms\Action\Kkt\EditAction::class,
        [
            'POST',
            'GET',
        ],
        'admin.kkt.edit'
    );
    $app->get('/lk/admin/kkt/{id:\d+}/close', \Cms\Action\Kkt\CloseShiftAction::class, 'admin.kkt.closeShift');
    $app->get('/lk/admin/kkt/{id:\d+}/close-fn', \Cms\Action\Kkt\CloseFnAction::class, 'admin.kkt.closeFn');
    $app->route(
        '/lk/admin/kkt/{id:\d+}/info[/{action:update|fiscal|close-report}]',
        \Cms\Action\Kkt\KktInfoAction::class,
        [
            'POST',
            'GET',
        ],
        'admin.kkt.info'
    );
    $app->get('/lk/admin/kkt/deleted', \Cms\Action\Kkt\DeletedAction::class, 'admin.kkt.deleted');


    $app->get('/lk/admin/company', \Cms\Action\Company\ListAction::class, 'admin.company.list');
    $app->route(
        '/lk/admin/company/{id:\d+}',
        \Cms\Action\Company\EditAction::class,
        [
            'GET',
            'POST',
        ],
        'admin.company.edit'
    );
    $app->delete('/lk/admin/company/{id:\d+}', \Cms\Action\Company\DeleteAction::class, 'admin.company.delete');
    $app->get('/lk/admin/service', \Cms\Action\Service\ListAction::class, 'admin.service');
    $app->route(
        '/lk/admin/service/{id:\d+}', \Cms\Action\Service\EditAction::class, $getPostMethods, 'admin.service.edit'
    );
    $app->get('/lk/admin/tariff', \Cms\Action\Tariff\ListAction::class, 'admin.tariff');
    $app->route(
        '/lk/admin/tariff/{id:\d+}', \Cms\Action\Tariff\EditAction::class, $getPostMethods, 'admin.tariff.edit'
    );
    $app->get('/lk/admin/file', \Cms\Action\File\ListAction::class, 'admin.file');
    $app->route('/lk/admin/file/{id:\d+}', \Cms\Action\File\EditAction::class, $getPostMethods, 'admin.file.edit');
    $app->get('/lk/admin/money-history', \Cms\Action\MoneyHistory\ListAction::class, 'admin.moneyHistory');
    $app->route('/lk/admin/money-history/{id:\d+}', \Cms\Action\MoneyHistory\EditAction::class, $getPostMethods, 'admin.moneyHistory.edit');

//api v1
    $app->post(
        '/lk/api/v1/token', [
        \ApiV1\Middleware\LoggerMiddlewareFactory::API_V1_NAME,
        \ApiV1\Action\TokenAction::class,
    ], 'api.v1.token'
    );
    $app->post(
        '/lk/api/v1/{shopId:\d+}/{operation:\w+}', [
        \ApiV1\Middleware\LoggerMiddlewareFactory::API_V1_NAME,
        \ApiV1\Middleware\CheckRequestMiddleware::class,
        \ApiV1\Action\RegisterCheckAction::class,
    ], 'api.v1.registerOperation'
    );
    $app->get(
        '/lk/api/v1/{shopId:\d+}/report',
        [
            \ApiV1\Middleware\LoggerMiddlewareFactory::API_V1_NAME,
            \ApiV1\Middleware\CheckRequestMiddleware::class,
            \ApiV1\Middleware\ProcessingMiddleware::class,
            \ApiV1\Action\ReportAction::class,
        ], 'api.v1.getProcessingExternal'
    );
    $app->get(
        '/lk/api/v1/{shopId:\d+}/report/{processingId:\d+}',
        [
            \ApiV1\Middleware\LoggerMiddlewareFactory::API_V1_NAME,
            \ApiV1\Middleware\CheckRequestMiddleware::class,
            \ApiV1\Middleware\ProcessingMiddleware::class,
            \ApiV1\Action\ReportAction::class,
        ], 'api.v1.getProcessing'
    );

//api Atol v1
    $app->route(
        '/lk/apiatol/v3/getToken', [
        \ApiV1\Middleware\LoggerMiddlewareFactory::API_ATOL_V1_NAME,
        \ApiAtolV1\Action\TokenAction::class,
    ],
        [
            'POST',
            'GET',
        ], 'apiatol.v1.token'
    );

    $app->post(
        '/lk/apiatol/v3/{shopId:\d+}/{operation:\w+}', [
        \ApiV1\Middleware\LoggerMiddlewareFactory::API_ATOL_V1_NAME,
        \ApiAtolV1\Middleware\CheckRequestMiddleware::class,
        \ApiAtolV1\Action\RegisterCheckAction::class,
    ], 'apiatol.v1.registerOperation'
    );

    $app->get(
        '/lk/apiatol/v3/{shopId:\d+}/report/{processingId:\d+}', [
        \ApiV1\Middleware\LoggerMiddlewareFactory::API_ATOL_V1_NAME,
        \ApiAtolV1\Middleware\CheckRequestMiddleware::class,
        \ApiAtolV1\Action\ReportAction::class,
    ], 'apiatol.v1.getProcessing'
    );

    //begin insales
    $app->get(
        '/insales/install',
        [
            \ApiInsales\Handler\InstallHandler::class,
        ],
        'insales.install'
    );

    $app->get(
        '/insales/login',
        [
            \ApiInsales\Handler\LoginHandler::class,
        ],
        'insales.login'
    );

    $app->get(
        '/insales/autologin',
        [
            \ApiInsales\Middleware\HookMiddleware::class,
            \ApiInsales\Handler\AutologinHandler::class,
        ],
        'insales.autologin'
    );

    $app->get(
        '/insales/settings',
        [
            \ApiInsales\Handler\SettingsViewHandler::class,
        ],
        'insales.settings.view'
    );

    $app->post(
        '/insales/settings',
        [
            \ApiInsales\Handler\SettingsEditHandler::class,
        ],
        'insales.settings.edit'
    );
    $app->post(
        '/insales/order/update',
        [
            \ApiInsales\Handler\PrintCheckHandler::class,
        ],
        'insales.printcheck'
    );

    $app->get(
        '/insales/uninstall',
        [
            \ApiInsales\Handler\UninstallHandler::class,
        ],
        'insales.uninstall'
    );
    $app->get(
        '/insales/manual',
        [
            \ApiInsales\Handler\ManualHandler::class,
        ],
        'insales.manual'
    );
    //end insales
};
