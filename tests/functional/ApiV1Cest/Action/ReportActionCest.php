<?php

namespace ApiV1Cest\Action;

use ApiV1\Action\Code;
use ApiV1\Action\ReportAction;
use App\Service\DateTime;
use Auth\Entity\User;
use Office\Entity\ApiKey;
use Office\Entity\Processing;
use Office\Entity\Shop;

class ReportActionCest
{
    private const SHOP_ID = 1;

    private $route = '/lk/api/v1/'.self::SHOP_ID.'/report';

    public function _before(\FunctionalTester $tester): void
    {
        $tester->haveHttpHeader('Content-Type', 'application/json');
    }

    public function _createToken(\FunctionalTester $tester): string
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

        return $token;
    }

//    public function testNotAllowedMethod(\FunctionalTester $tester): void
//    {
//        //        $tester->addTestApiKeyAccess();
//        $user = $tester->grabEntityFromRepository(User::class, ['email' => 'testapi@schetmash.com']);
//        $login = 'test_api';
//        $password = hash('ripemd128', '123456');
//        $token = uniqid((string)time());
//
//        $tester->haveInRepository(
//            ApiKey::class, [
//                'login'            => $login,
//                'password'         => $password,
//                'user'             => $user,
//                'token'            => $token,
//                'dateCreate'       => new \DateTime(),
//                'dateExpiredToken' => (new \DateTime())->add(new \DateInterval('P1D')),
//            ]
//        );
//
//        $tester->sendAjaxPostRequest($this->route.'?token='.$token);
//        $tester->seeResponseCodeIs(405);
//    }

    public function testIncorrectProcessingId(\FunctionalTester $tester): void
    {
        //        $tester->addTestApiKeyAccess();
        $token = $this->_createToken($tester);

        $tester->sendGET($this->route.'/1', ['token' => $token]);
        $tester->seeResponseCodeIs(200);
        $tester->seeResponseContainsJson(
            [
                'code'    => Code::INCORRECT_PROCESSING_ID,
                'message' => Code::getMessage(Code::INCORRECT_PROCESSING_ID),
            ]
        );
    }

    public function testEmptyExternalId(\FunctionalTester $tester): void
    {
        //        $tester->addTestApiKeyAccess();
        $token = $this->_createToken($tester);

        $tester->sendGET($this->route, ['token' => $token]);
        $tester->seeResponseCodeIs(200);
        $tester->seeResponseContainsJson(
            [
                'code'    => Code::INCORRECT_PROCESSING_ID,
                'message' => Code::getMessage(Code::INCORRECT_PROCESSING_ID),
            ]
        );
    }

    public function testPrintError(\FunctionalTester $tester): void
    {
        $token = $this->_createToken($tester);
        $shop = $tester->grabEntityFromRepository(Shop::class, ['id' => 1]);
        $id = $tester->haveInRepository(
            Processing::class, [
                'shop'      => $shop,
                'error'     => 'my error',
                'datetime'  => new \DateTime(),
                'status'    => Processing::STATUS_ACCEPT,
                'operation' => Processing::OPERATION_SELL,
            ]
        );
        $tester->sendGET($this->route.'/'.$id, ['token' => $token]);
        $tester->seeResponseCodeIs(200);
        $tester->seeResponseContainsJson(
            [
                'id'      => $id,
                'status'  => 'error',
                'payload' => null,
                'error'   => [
                    'code'    => Code::INCORECT_DATA,
                    'message' => 'my error',
                ],
            ]
        );
    }

    public function testPrintAccept(\FunctionalTester $tester): void
    {
        $token = $this->_createToken($tester);
        $shop = $tester->grabEntityFromRepository(Shop::class, ['id' => 1]);
        $id = $tester->haveInRepository(
            Processing::class, [
                'shop'      => $shop,
                'datetime'  => new \DateTime(),
                'status'    => Processing::STATUS_ACCEPT,
                'operation' => Processing::OPERATION_SELL,
            ]
        );
        $tester->sendGET($this->route.'/'.$id, ['token' => $token]);
        $tester->seeResponseCodeIs(200);
        $tester->seeResponseContainsJson(
            [
                'id'     => $id,
                'status' => 'accept',
            ]
        );
    }

    public function testPrintAcceptForExternalId(\FunctionalTester $tester): void
    {
        $token = $this->_createToken($tester);
        $shop = $tester->grabEntityFromRepository(Shop::class, ['id' => 1]);
        $id = $tester->haveInRepository(
            Processing::class, [
                'shop'       => $shop,
                'datetime'   => new \DateTime(),
                'status'     => Processing::STATUS_ACCEPT,
                'operation'  => Processing::OPERATION_SELL,
                'externalId' => 123,
            ]
        );
        $tester->sendGET(
            $this->route, [
                'token'       => $token,
                'external_id' => 123,
            ]
        );
        $tester->seeResponseCodeIs(200);
        $tester->seeResponseContainsJson(
            [
                'id'     => $id,
                'status' => 'accept',
            ]
        );
    }

    public function testPrintPrepare(\FunctionalTester $tester): void
    {
        $token = $this->_createToken($tester);
        $shop = $tester->grabEntityFromRepository(Shop::class, ['id' => 1]);
        $id = $tester->haveInRepository(
            Processing::class, [
                'shop'      => $shop,
                'datetime'  => new \DateTime(),
                'status'    => Processing::STATUS_PREPARE,
                'operation' => Processing::OPERATION_SELL,
            ]
        );
        $tester->sendGET($this->route.'/'.$id, ['token' => $token]);
        $tester->seeResponseCodeIs(200);
        $tester->seeResponseContainsJson(
            [
                'id'     => $id,
                'status' => 'processing',
            ]
        );
    }

    public function testPrintSuccessNormalCheck(\FunctionalTester $tester): void
    {
        $token = $this->_createToken($tester);
        $shop = $tester->grabEntityFromRepository(Shop::class, ['id' => 1]);
        $id = $tester->haveInRepository(
            Processing::class, [
                'shop'                  => $shop,
                'datetime'              => new \DateTime(),
                'status'                => Processing::STATUS_SEND_CLIENT,
                'operation'             => Processing::OPERATION_SELL,
                'sum'                   => 249,
                'fnNumber'              => '9252440300117773',
                'shiftNumber'           => 48,
                'datePrint'             => new DateTime('04 Jul 2019 13:28:44 +0300'),
                'receiptNumber'         => 762,
                'docNumber'             => 45211,
                'ecrRegistrationNumber' => '0003149768014213',
                'documentAttribute'     => 3906843070,
            ]
        );
        $tester->sendGET($this->route.'/'.$id, ['token' => $token]);
        $tester->seeResponseCodeIs(200);
        $tester->seeResponseContainsJson(
            [
                'id'      => $id,
                'status'  => 'success',
                'payload' => [
                    'total'                     => 249,
                    'fns_site'                  => 'www.nalog.ru',
                    'fn_number'                 => '9252440300117773',
                    'shift_number'              => 48,
                    'receipt_datetime'          => '04.07.2019 13:28:44',
                    'fiscal_receipt_number'     => 762,
                    'fiscal_document_number'    => 45211,
                    'ecr_registration_number'   => '0003149768014213',
                    'fiscal_document_attribute' => 3906843070,
                    'qr'                        => null,
                ],
            ]
        );
    }
}
