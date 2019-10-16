<?php


namespace ApiInsalesCest\Handler;


use ApiInsales\Entity\InsalesShop;
use ApiInsales\Service\InsalesSettingsService;
use App\Helper\UrlHelper;
use Auth\Entity\User;
use Auth\Service\AuthenticationService;
use Codeception\Util\Stub;
use Zend\Expressive\Session\Ext\PhpSessionPersistence;
use Zend\Expressive\Session\Session;

class LoginHandlerCest
{
    private const TEST_USER_ID = 2;

    protected $insalesId;

    protected $shopInsales;

    protected $userId;

    protected $user;

    protected $password;

    /**
     * @param \FunctionalTester $tester
     * входные данные (@throws \Exception
     *
     *@todo m-lobanov переделать с использованием DataProvider)
     */
    public function _before(\FunctionalTester $tester): void
    {
        $this->user = $tester->grabEntityFromRepository(User::class, ['id' => self::TEST_USER_ID]);
        $this->insalesId = rand(100000, 999999);
        $this->userId = rand(100000, 999999);
        $this->shopInsales = 'myshop-ww'.rand(1, 99999).'.myinsales.ru';
        $this->password = md5(uniqid());

        $tester->setService(
            AuthenticationService::class, \Codeception\Stub::make(
            AuthenticationService::class, [
                'authenticate' => function () use ($tester) {
                    return $tester->grabEntityFromRepository(User::class, ['id' => self::TEST_USER_ID]);
                },
            ]
        )
        );

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

    /**
     * @param \FunctionalTester $tester
     * Подготовка данных и отправка на автологин
     *
     * @throws \Exception
     */
    public function redirectToAutologin(\FunctionalTester $tester): void
    {
        //$tester->setCookie('PHPSESSID', 'el4ukv0kqbvoirg7nkp4dncpk3');
        $tester->followRedirects(false);

        $tester->sendGET(
            '/insales/login', [
                'insales_id' => $this->insalesId,
                'shop'       => $this->shopInsales,
                'user_email' => 'test@test.test',
                'user_id'    => $this->userId,
            ]
        );
//        $cookie = $tester->grabCookie('PHPSESSID');
//        $sessionFile = file_get_contents(ini_get('session.save_path').'/sess_'.$cookie);
//        $session = self::unserialize_php($sessionFile);
        //codecept_debug($session); // $session is an array. Run Codeception with `--debug` to see it
//        $tester->assertArrayHasKey('insales_token', $session);
        $tester->seeResponseCodeIs(302);
        $tester->seeInRepository(
            InsalesShop::class, [
                'userId'        => $this->userId,
                'password'      => $this->password,
                'insalesId'     => $this->insalesId,
                'shopInsales'   => $this->shopInsales,
                'userSchetmash' => $this->user,
            ]
        );
//        $config = $tester->getService('config');
        /** @var UrlHelper $urlHelper */
//        $urlHelper = $tester->getService(UrlHelper::class);
//        $tester->seeHttpHeader(
//            'location', 'http://'.$this->shopInsales.'/admin/applications/'.InsalesSettingsService::APP_ID.'/login'.
//            '?token='.$session['insales_token'].'&login='.'https://'.$config['APP_DOMAIN'].$urlHelper->generate('insales.autologin')
//        );
    }

    /**
     * @param \FunctionalTester $tester
     * Отправка на страницу настроек (@throws \Exception
     *
     * @todo если есть insales_login в сессии)
     */
    public function redirectToSettings(\FunctionalTester $tester): void
    {
        $tester->followRedirects(false);
        /** @var InsalesSettingsService $insalesSettingsService */
        $insalesSettingsService = $tester->getService(InsalesSettingsService::class);
        $tester->setService(
            PhpSessionPersistence::class,
            Stub::make(PhpSessionPersistence::class, ['initializeSessionFromRequest' => new Session([$insalesSettingsService->getSessionLoginName($this->userId) => true])])
        );
        $tester->sendGET(
            '/insales/login', [
                'insales_id' => $this->insalesId,
                'shop'       => $this->shopInsales,
                'user_email' => 'test@test.test',
                'user_id'    => $this->userId,
            ]
        );
        $tester->seeResponseCodeIs(302);
        $urlHelper = $tester->getService(UrlHelper::class);
        $tester->seeHttpHeader(
            'location', $urlHelper->generate(
            'insales.settings.view',
            [],
            [
                'user_id' => $this->userId,
            ]
        )
        );
        $tester->seeInRepository(
            InsalesShop::class, [
                'userSchetmash' => $this->user,
            ]
        );
    }

    /**
     * Метод для получения массива сессии
     *
     * @param $session_data
     *
     * @return array
     * @throws \Exception
     */
//    private static function unserialize_php($session_data)
//    {
//        $return_data = [];
//        $offset = 0;
//        while ($offset < strlen($session_data)) {
//            if (!strstr(substr($session_data, $offset), "|")) {
//                throw new \Exception("invalid data, remaining: ".substr($session_data, $offset));
//            }
//            $pos = strpos($session_data, "|", $offset);
//            $num = $pos - $offset;
//            $varname = substr($session_data, $offset, $num);
//            $offset += $num + 1;
//            $data = unserialize(substr($session_data, $offset));
//            $return_data[$varname] = $data;
//            $offset += strlen(serialize($data));
//        }
//
//        return $return_data;
//    }
}