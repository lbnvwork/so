<?php


namespace ApiInsalesCest\Handler;


use ApiInsales\Entity\InsalesShop;
use Auth\Entity\User;
use Auth\Service\AuthenticationService;
use Codeception\Stub;

class UninstallHandlerCest
{
    protected $insalesId;

    protected $shopInsales;

    protected $userId;

    protected $password;

    protected $user;

    public function _before(\FunctionalTester $tester): void
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

        $this->insalesId = 706302;
        $this->shopInsales = 'myshop-ww864.myinsales.ru';
        $this->userId = 770141;
        $this->password = 'f669c2c1abb4fe4284bc317da375a83f';
        $this->user = $tester->grabEntityFromRepository(User::class, ['id' => 2]);
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
    }

    public function successUninstall(\FunctionalTester $tester): void
    {
        $tester->sendGET(
            '/insales/uninstall', [
                'insales_id' => $this->insalesId,
                'shop'       => $this->shopInsales,
                'token'      => $this->password,
            ]
        );
        $tester->seeResponseCodeIs(200);
        $tester->dontSeeInRepository(
            InsalesShop::class, [
                'insalesId'     => $this->insalesId,
                'shopInsales'   => $this->shopInsales,
                'password'      => $this->password,
                'userSchetmash' => $this->user,
            ]
        );
    }
}