<?php


namespace ApiInsalesCest\Handler;


use ApiInsales\Entity\InsalesShop;
use ApiInsales\Service\InsalesSettingsService;
use ApiInsales\Service\WebhookCurlService;
use App\Helper\UrlHelper;
use Auth\Entity\User;
use Codeception\Stub;
use Zend\Expressive\Session\Ext\PhpSessionPersistence;
use Zend\Expressive\Session\Session;


class AutologinHandlerCest
{
    private const TEST_USER_ID = 2;

    public function successAutologin(\FunctionalTester $tester)
    {
        $user = $tester->grabEntityFromRepository(User::class, ['id' => self::TEST_USER_ID]);
        $tester->followRedirects(false);
        $insalesId = rand(100000, 999999);
        $userId = rand(100000, 999999);;
        $shopInsales = 'myshop-ww'.rand(1, 99999).'.myinsales.ru';
        $password = md5(uniqid());


        $tester->haveInRepository(
            InsalesShop::class,
            [
                'password'      => $password,
                'insalesId'     => $insalesId,
                'shopInsales'   => $shopInsales,
                'userId'        => $userId,
                'userSchetmash' => $user,
            ]
        );
        /** @var UrlHelper $urlHelper */
        $urlHelper = $tester->getService(UrlHelper::class);

        $getParamsKeys = [
            'token2',
            'token',
            'token3',
            'user_email',
            'user_name',
            'user_id',
            'email_confirmed',
        ];
        $token = md5(uniqid());
        $email = 'test@test.test';
        $userName = 'testUserName';
        $emailConfirmed = 'false';
        $token3 = md5($token.$email.$userName.$userId.$emailConfirmed.$password);
        $getParamValues = [
            md5(uniqid()),
            md5(uniqid()),
            $token3,
            //token3
            $email,
            //email
            $userName,
            //user_name
            $userId,
            //user_id
            $emailConfirmed
            //email_confirmed
        ];
        /** @var InsalesSettingsService $insalesSettingsService */
        $insalesSettingsService = $tester->getService(InsalesSettingsService::class);
        $tester->setService(
            PhpSessionPersistence::class,
            Stub::make(
                PhpSessionPersistence::class,
                [
                    'initializeSessionFromRequest' => new Session([$insalesSettingsService->getSessionTokenName($userId) => $token]),
                ]
            )
        );
        $tester->setService(
            WebhookCurlService::class,
            Stub::make(WebhookCurlService::class, ['addWebhook' => $res['httpCode'] = '201'])
        );
        $getParams = array_combine($getParamsKeys, $getParamValues);
        $tester->sendGET(
            $urlHelper->generate('insales.autologin'),
            $getParams
        );
        $tester->seeResponseCodeIs(302);
        $tester->seeHttpHeader(
            'location', $urlHelper->generate(
            'insales.login',
            [],
            [
                'insales_id' => $insalesId,
                'shop'       => $shopInsales,
                'user_email' => $email,
                'user_id'    => $userId,
            ]
        )
        );
    }
}