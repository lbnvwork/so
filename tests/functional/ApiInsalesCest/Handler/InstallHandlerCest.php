<?php
declare(strict_types=1);

namespace ApiInsalesCest\Handler;

use ApiInsales\Entity\InsalesShop;
use ApiInsales\Service\InsalesSettingsService;
use Auth\Entity\User;
use Auth\Service\AuthenticationService;
use Codeception\Stub;

class InstallHandlerCest
{
    public function _before(\FunctionalTester $tester)
    {
        $tester->setService(
            AuthenticationService::class, Stub::make(
            AuthenticationService::class, [
                'authenticate' => function () use ($tester) {
                    return $tester->grabEntityFromRepository(User::class, ['id' => 2]);
                },
            ]
        )
        );
    }

    public function successInstall(\FunctionalTester $tester): void
    {
        $insalesId = '123';
        $shopInsales = 'test.myinsales.ru';
        $token = 'token';
        /** @var InsalesSettingsService $insalesSettingsService */
        $insalesSettingsService = $tester->getService(InsalesSettingsService::class);
        $secretKey = $insalesSettingsService->getAppSecretKey();
        $tester->sendGET(
            '/insales/install', [
                'insales_id' => $insalesId,
                'shop'       => $shopInsales,
                'token'      => $token,
            ]
        );
        $tester->seeResponseCodeIs(200);

        $tester->seeInRepository(
            InsalesShop::class, [
                'insalesId'     => $insalesId,
                'shopInsales'   => $shopInsales,
                'password'      => md5($token.$secretKey),
                //'userSchetmash' => $tester->grabEntityFromRepository(User::class, ['id' => 2]),
            ]
        );
    }
}