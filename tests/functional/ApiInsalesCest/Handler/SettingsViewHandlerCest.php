<?php


namespace ApiInsalesCest\Handler;


use ApiInsales\Entity\InsalesShop;
use Auth\Entity\User;
use Auth\Service\AuthenticationService;
use Codeception\Stub;

class SettingsViewHandlerCest
{

    private const TEST_USER_ID = 2;

    protected $insalesId;

    protected $shopInsales;

    protected $userId;

    protected $user;

    protected $password;

    public function _before(\FunctionalTester $tester)
    {
        $this->userId = rand(100000, 999999);

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

    public function haveNoUserId(\FunctionalTester $tester): void
    {
        $tester->sendGET(
            '/insales/settings'
        );

        $tester->seeResponseCodeIs(400);
    }

    public function successView(\FunctionalTester $tester): void
    {
        $this->user = $tester->grabEntityFromRepository(User::class, ['id' => self::TEST_USER_ID]);
        $this->insalesId = rand(100000, 999999);
        $this->userId = rand(100000, 999999);
        $this->shopInsales = 'myshop-ww'.rand(1, 99999).'.myinsales.ru';
        $this->password = md5(uniqid());


        $tester->haveInRepository(
            InsalesShop::class,
            [
                'password'      => $this->password,
                'insalesId'     => $this->insalesId,
                'shopInsales'   => $this->shopInsales,
                'userId'        => $this->userId,
                'userSchetmash' => $this->user,
            ]
        );

        $tester->sendGET(
            '/insales/settings', [
                'user_id' => $this->userId,
            ]
        );

        $tester->seeResponseCodeIs(200);
    }
}