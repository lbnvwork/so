<?php
declare(strict_types=1);

namespace ApiV1Cest\Action;

use ApiV1\Action\Code;
use Auth\Entity\User;
use Office\Entity\ApiKey;
use Symfony\Component\VarDumper\VarDumper;
use Zend\Json\Json;

class TokenActionCest
{
    private $route = '/lk/api/v1/token';

    public function _before(\FunctionalTester $tester): void
    {
        $tester->haveHttpHeader('Content-Type', 'application/json');
    }

    public function testNotAllowedMethod(\FunctionalTester $tester): void
    {
        $tester->sendAjaxGetRequest($this->route);
        $tester->seeResponseCodeIs(405);
    }

    public function testEmptyRequest(\FunctionalTester $tester): void
    {
        $tester->sendAjaxPostRequest($this->route);
        $tester->seeResponseCodeIs(400);
    }

    public function sendIncorrectLogin(\FunctionalTester $tester): void
    {
        $tester->sendPOST($this->route, ['password' => 'test']);
        $tester->seeResponseCodeIs(400);
        $tester->seeResponseIsJson();
        $tester->seeResponseEquals(
            Json::encode(
                [
                    'message' => 'Неверный логин или пароль',
                    'token'   => null,
                    'code'    => 1,
                ]
            )
        );
    }

    public function sendIncorrectPassword(\FunctionalTester $tester): void
    {
        $tester->sendPOST($this->route, ['login' => 'test']);
        $tester->seeResponseCodeIs(400);
        $tester->seeResponseIsJson();
        $tester->seeResponseEquals(
            Json::encode(
                [
                    'message' => 'Неверный логин или пароль',
                    'token'   => null,
                    'code'    => 1,
                ]
            )
        );
    }

    public function sendIncorrectCredintals(\FunctionalTester $tester): void
    {
        $tester->sendPOST(
            $this->route, [
                'login'    => 'test',
                'password' => 'test',
            ]
        );
        $tester->seeResponseCodeIs(400);
        $tester->seeResponseIsJson();
        $tester->seeResponseEquals(
            Json::encode(
                [
                    'message' => 'Неверный логин или пароль',
                    'token'   => null,
                    'code'    => 1,
                ]
            )
        );
    }

    public function successGetToken(\FunctionalTester $tester): void
    {
//        $tester->addTestApiKeyAccess();
        $user = $tester->grabEntityFromRepository(User::class, ['email' => 'testapi@schetmash.com']);
        $login = 'test_api';
        $password = hash('ripemd128', '123456');

        $tester->haveInRepository(
            ApiKey::class, [
                'login'      => $login,
                'password'   => $password,
                'user'       => $user,
                'dateCreate' => new \DateTime(),
            ]
        );

        $tester->sendPOST(
            $this->route, [
                'login'    => 'test_api ',
                'password' => '123456',
            ]
        );
        $tester->seeResponseCodeIs(200);
        $tester->seeResponseIsJson();
        $tester->seeResponseContainsJson(
            [
                'message' => null,
                'code'    => Code::USE_NEW_TOKEN,
            ]
        );
    }

    public function successGetOldToken(\FunctionalTester $tester): void
    {
//        $tester->addTestApiKeyAccess();
        $user = $tester->grabEntityFromRepository(User::class, ['email' => 'testapi@schetmash.com']);
        $login = 'test_api';
        $password = hash('ripemd128', '123456');

        $tester->haveInRepository(
            ApiKey::class, [
                'login'            => $login,
                'password'         => $password,
                'user'             => $user,
                'token'            => uniqid((string)time()),
                'dateCreate'       => new \DateTime(),
                'dateExpiredToken' => (new \DateTime())->add(new \DateInterval('P1D')),
            ]
        );
        $tester->sendPOST(
            $this->route, [
                'login'    => 'test_api ',
                'password' => '123456',
            ]
        );

        $tester->seeResponseCodeIs(200);
        $tester->seeResponseIsJson();
        $tester->seeResponseContainsJson(
            [
                'message' => null,
                'code'    => Code::USE_OLD_TOKEN,
            ]
        );
    }
}
