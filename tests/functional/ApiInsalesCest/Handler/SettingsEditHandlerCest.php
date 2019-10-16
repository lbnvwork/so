<?php
declare(strict_types=1);

namespace ApiInsalesCest\Handler;


use ApiInsales\Entity\InsalesShop;
use Auth\Entity\User;
use Auth\Service\AuthenticationService;
use Codeception\Stub;
use Office\Entity\Shop;

class SettingsEditHandlerCest
{
    private const TEST_USER_ID = 2;

    protected $insalesId;

    protected $shopInsales;

    protected $userId;

    protected $user;

    public function _before(\FunctionalTester $tester): void
    {
        $this->insalesId = rand(100000, 999999);
        $this->shopInsales = 'myshop-ww'.rand(1, 99999).'.myinsales.ru';
        $this->userId = rand(100000, 999999);

        $this->user = $tester->grabEntityFromRepository(User::class, ['id' => self::TEST_USER_ID]);
        $tester->haveInRepository(
            InsalesShop::class,
            [
                'password'      => md5(uniqid()),
                'insalesId'     => $this->insalesId,
                'shopInsales'   => $this->shopInsales,
                'userId'        => $this->userId,
                'userSchetmash' => $this->user,
            ]
        );
        //Сделал эмуляцию сервиса, переопределил метод authenticate
        $tester->setService(
            AuthenticationService::class, Stub::make(
            AuthenticationService::class, [
                'authenticate' => function () use ($tester) {
                    return $tester->grabEntityFromRepository(User::class, ['id' => self::TEST_USER_ID]);
                },
            ]
        )
        );
    }

    public function successEdit(\FunctionalTester $tester): void
    {
        $shopId = '1';
        $shop = $tester->grabEntityFromRepository(Shop::class, ['id' => $shopId]);
        $tester->sendPOST(
            '/insales/settings?'.http_build_query(
                [
                    'user_id' => $this->userId,
                ]
            ),
            [
                'shopSchetmash' => $shopId,
            ]
        );
        $tester->seeResponseCodeIs(200);
        $tester->seeInRepository(
            InsalesShop::class, [
                'shopSchetmash' => $shop,
            ]
        );
    }
}