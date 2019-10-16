<?php

namespace ApiV1Cest\Action;

use ApiV1\Action\Code;
use ApiV1\Action\RegisterCheckAction;
use App\Service\DateTime;
use Auth\Entity\User;
use Office\Entity\ApiKey;
use Office\Entity\Company;
use Office\Entity\Processing;
use Office\Entity\Shop;

class RegisterCheckActionCest
{
    private const SHOP_ID = 1;

    private $route = '/lk/api/v1/'.self::SHOP_ID.'/';

    public function _before(\FunctionalTester $tester): void
    {
        $tester->haveHttpHeader('Content-Type', 'application/json');
    }

    public function testNotAllowedMethod(\FunctionalTester $tester): void
    {
        $tester->sendAjaxGetRequest($this->route.'sell');
        $tester->seeResponseCodeIs(405);
    }

    public function notSendToken(\FunctionalTester $tester): void
    {
        $tester->sendAjaxPostRequest($this->route.'sell');
        $tester->seeResponseCodeIs(200);
        $tester->seeResponseContainsJson(
            [
                'code'    => Code::INCORRECT_TOKEN,
                'message' => Code::getMessage(Code::INCORRECT_TOKEN),
            ]
        );
    }

    public function sendIncorrectToken(\FunctionalTester $tester): void
    {
        $tester->sendAjaxPostRequest($this->route.'sell?token=123456789');
        $tester->seeResponseCodeIs(200);
        $tester->seeResponseContainsJson(
            [
                'code'    => Code::INCORRECT_TOKEN,
                'message' => Code::getMessage(Code::USE_OLD_TOKEN),
            ]
        );
    }

    public function sendIncorrectShop(\FunctionalTester $tester): void
    {
        //        $tester->addTestApiKeyAccess();
        $user = $tester->grabEntityFromRepository(User::class, ['email' => 'testapi@schetmash.com']);
        $login = 'test_api';
        $password = hash('ripemd128', '123456');
        $token = uniqid((string)time());

        $tester->haveInRepository(
            ApiKey::class, [
                'login'            => $login,
                'password'         => $password,
                'user'             => $user,
                'token'            => $token,
                'dateCreate'       => new \DateTime(),
                'dateExpiredToken' => (new \DateTime())->add(new \DateInterval('P1D')),
            ]
        );

        $tester->sendAjaxPostRequest('/lk/api/v1/2/sell?token='.$token);
        $tester->seeResponseCodeIs(200);
        $tester->seeResponseContainsJson(
            [
                'code'    => Code::INCORRECT_SHOP,
                'message' => Code::getMessage(Code::INCORRECT_SHOP),
            ]
        );
    }

    public function sendIncorrectOperation(\FunctionalTester $tester): void
    {
        //        $tester->addTestApiKeyAccess();
        $user = $tester->grabEntityFromRepository(User::class, ['email' => 'testapi@schetmash.com']);
        $login = 'test_api';
        $password = hash('ripemd128', '123456');
        $token = uniqid((string)time());

        $tester->haveInRepository(
            ApiKey::class, [
                'login'            => $login,
                'password'         => $password,
                'user'             => $user,
                'token'            => $token,
                'dateCreate'       => new \DateTime(),
                'dateExpiredToken' => (new \DateTime())->add(new \DateInterval('P1D')),
            ]
        );

        $tester->sendAjaxPostRequest($this->route.'aaaaa?token='.$token);
        $tester->seeResponseCodeIs(200);
        $tester->seeResponseContainsJson(
            [
                'code'    => Code::INCORRECT_OPERATION,
                'message' => Code::getMessage(Code::INCORRECT_OPERATION),
            ]
        );
    }

    public function notFoundKkt(\FunctionalTester $tester): void
    {
        //        $tester->addTestApiKeyAccess();
        $user = $tester->grabEntityFromRepository(User::class, ['email' => 'testapi@schetmash.com']);
        $login = 'test_api';
        $password = hash('ripemd128', '123456');
        $token = uniqid((string)time());

        $tester->haveInRepository(
            ApiKey::class, [
                'login'            => $login,
                'password'         => $password,
                'user'             => $user,
                'token'            => $token,
                'dateCreate'       => new \DateTime(),
                'dateExpiredToken' => (new \DateTime())->add(new \DateInterval('P1D')),
            ]
        );
        $company = $tester->grabEntityFromRepository(Company::class, ['id' => 1]);
        $tester->haveInRepository(
            Shop::class, [
                'company' => $company,
                'title'   => 'test',
            ]
        );

        $tester->sendAjaxPostRequest('/lk/api/v1/2/sell?token='.$token);
        $tester->seeResponseCodeIs(200);
        $tester->seeResponseContainsJson(
            [
                'code'    => Code::NOT_FOUND_KKT,
                'message' => Code::getMessage(Code::NOT_FOUND_KKT),
            ]
        );
    }

    public function sendInvalidJson(\FunctionalTester $tester): void
    {
        //        $tester->addTestApiKeyAccess();
        $user = $tester->grabEntityFromRepository(User::class, ['email' => 'testapi@schetmash.com']);
        $login = 'test_api';
        $password = hash('ripemd128', '123456');
        $token = uniqid((string)time());

        $tester->haveInRepository(
            ApiKey::class, [
                'login'            => $login,
                'password'         => $password,
                'user'             => $user,
                'token'            => $token,
                'dateCreate'       => new \DateTime(),
                'dateExpiredToken' => (new \DateTime())->add(new \DateInterval('P1D')),
            ]
        );

        $tester->sendAjaxPostRequest($this->route.'sell?token='.$token);
        $tester->seeResponseCodeIs(200);
        $tester->seeResponseContainsJson(
            [
                'code'    => Code::INCORECT_DATA,
                'message' => Code::getMessage(Code::INCORECT_DATA).', Decoding failed: Syntax error',
            ]
        );
    }

    public function sendEmptyExternalId(\FunctionalTester $tester): void
    {
        //        $tester->addTestApiKeyAccess();
        $user = $tester->grabEntityFromRepository(User::class, ['email' => 'testapi@schetmash.com']);
        $login = 'test_api';
        $password = hash('ripemd128', '123456');
        $token = uniqid((string)time());

        $tester->haveInRepository(
            ApiKey::class, [
                'login'            => $login,
                'password'         => $password,
                'user'             => $user,
                'token'            => $token,
                'dateCreate'       => new \DateTime(),
                'dateExpiredToken' => (new \DateTime())->add(new \DateInterval('P1D')),
            ]
        );

        $tester->sendPOST($this->route.'sell?token='.$token, []);
        $tester->seeResponseCodeIs(200);
        $tester->seeResponseContainsJson(
            [
                'code'    => Code::INCORECT_DATA,
                'message' => Code::getMessage(Code::INCORECT_DATA).', не корректный json',
            ]
        );
    }

    public function sendCheck(\FunctionalTester $tester): void
    {
        //        $tester->addTestApiKeyAccess();
        $user = $tester->grabEntityFromRepository(User::class, ['email' => 'testapi@schetmash.com']);
        $login = 'test_api';
        $password = hash('ripemd128', '123456');
        $token = uniqid((string)time());

        $tester->haveInRepository(
            ApiKey::class, [
                'login'            => $login,
                'password'         => $password,
                'user'             => $user,
                'token'            => $token,
                'dateCreate'       => new \DateTime(),
                'dateExpiredToken' => (new \DateTime())->add(new \DateInterval('P1D')),
            ]
        );

        $check = [
            'external_id' => 1,
            'receipt'     => [
                'total'    => 10,
                'items'    => [
                    [
                        'name'     => 'test',
                        'price'    => 10,
                        'quantity' => 2,
                        'tax'      => 'none',
                    ],
                ],
                'payments' => [
                    [
                        'type' => 1,
                        'sum'  => 10,
                    ],
                ],
            ],
            'service'     => [
                'callback_url' => 'test',
            ],
        ];

        $tester->sendPOST($this->route.'sell?token='.$token, $check);
        $tester->seeResponseCodeIs(200);
        $tester->seeResponseContainsJson(
            [
                'status' => 'accept',
            ]
        );
        $tester->assertCount(1, $tester->grabEntitiesFromRepository(Processing::class));
    }

    public function testDuplicateExternalId(\FunctionalTester $tester): void
    {
        $user = $tester->grabEntityFromRepository(User::class, ['email' => 'testapi@schetmash.com']);
        $login = 'test_api';
        $password = hash('ripemd128', '123456');
        $token = uniqid((string)time());

        $tester->haveInRepository(
            ApiKey::class, [
                'login'            => $login,
                'password'         => $password,
                'user'             => $user,
                'token'            => $token,
                'dateCreate'       => new \DateTime(),
                'dateExpiredToken' => (new \DateTime())->add(new \DateInterval('P1D')),
            ]
        );
        $tester->haveInRepository(
            Processing::class, [
                'externalId' => 1,
                'shop'        => $tester->grabEntityFromRepository(Shop::class, ['id' => 1]),
                'datetime'    => new DateTime(),
                'status'      => 1,
                'operation'   => 1,
            ]
        );

        $check = [
            'external_id' => 1,
            'receipt'     => [
                'total'    => 10,
                'items'    => [
                    [
                        'name'     => 'test',
                        'price'    => 10,
                        'quantity' => 2,
                        'tax'      => 'none',
                    ],
                ],
                'payments' => [
                    [
                        'type' => 1,
                        'sum'  => 10,
                    ],
                ],
            ],
            'service'     => [
                'callback_url' => 'test',
            ],
        ];

        $tester->sendPOST($this->route.'sell?token='.$token, $check);
        $tester->seeResponseCodeIs(200);
        $tester->seeResponseContainsJson(
            [
                'status' => 'accept',
            ]
        );
        $tester->assertCount(1, $tester->grabEntitiesFromRepository(Processing::class));
    }
}
