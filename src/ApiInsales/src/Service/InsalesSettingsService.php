<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 03.07.19
 * Time: 15:24
 */

namespace ApiInsales\Service;

use App\Helper\UrlHelper;
use Doctrine\ORM\EntityManager;

/**
 * Class InsalesSettingsService
 * сервис общих настроек приложения insales
 *
 * @package ApiInsales\Service
 */
class InsalesSettingsService
{

    private const APP_TITLE = 'Счетмаш-онлайн';

    public const APP_ID = 'schetmash';

    public const APP_SECRET_KEY = 'SoS';

    public const APP_DESC =
        'Счетмаш Онлайн» - это веб-касса для сайтов, где за товары или услуги можно расплатиться онлайн. 
        С помощью этого сервиса покупатели получают электронные чеки, а Ваш бизнес в интернете соответствует 54-ФЗ.';

    public const APP_COMPANY_NAME = 'АО "Cчётмаш"';

    public const APP_CONTACTS = '305022, г. Курск, ул. 2-я Рабочая, 23, литер В2, помещение 53, тел. +78007005409';

    public const APP_LOGO = ROOT_PATH.'/upload/content/insales_logo.png';

    public const SESSION_LOGIN_NAME = 'insales_login_';

    public const SESSION_AUTOLOGIN_TOKEN_NAME = 'insales_token_';

    public const GET_LOGIN_KEYS = [
        'insales_id',
        'shop',
        'user_email',
        'user_id',
    ];

    public const GET_AUTOLOGIN_KEYS = [
        'token',
        'token2',
        'token3',
        'user_email',
        'user_name',
        'user_id',
        'email_confirmed',
    ];

    private $appInstallUrl;

    private $appLoginUrl;

    private $appAutologinUrl;

    private $appUninstallUrl;

    private $appHookUrl;

    private $urlHelper;
    private $config;

    public function __construct(
        UrlHelper $urlHelper,
        array $config
    ) {
        $this->urlHelper = $urlHelper;
        $this->config = $config;
        $this->appInstallUrl = 'https://'.$this->config['APP_DOMAIN'].$this->urlHelper->generate('insales.install');
        $this->appLoginUrl = 'https://'.$this->config['APP_DOMAIN'].$this->urlHelper->generate('insales.login');
        $this->appAutologinUrl = 'https://'.$this->config['APP_DOMAIN'].$this->urlHelper->generate('insales.autologin');
        $this->appUninstallUrl = 'https://'.$this->config['APP_DOMAIN'].$this->urlHelper->generate('insales.uninstall');
        $this->appHookUrl = 'https://'.$this->config['APP_DOMAIN'].$this->urlHelper->generate('insales.printcheck');
    }

    /**
     * Секретный ключ приложения insales
     *
     * @return string
     */
    public function getAppSecretKey()
    {
        return self::APP_SECRET_KEY;
    }

    /**
     * Идентификатор приложения insales
     *
     * @return string
     */
    public function getAppId()
    {
        return self::APP_ID;
    }

    /**
     * @return string
     */
    public function getInstallUrl()
    {
        return $this->appInstallUrl;
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->appLoginUrl;
    }

    /**
     * Страница автологина (куда приходит ответ из insales, на запрос автологина)
     *
     * @return string
     */
    public function getAutologinUrl()
    {
        return $this->appAutologinUrl;
    }

    /**
     * Ссылка на удаление приложения
     *
     * @return string
     */
    public function getUninstallUrl()
    {
        return $this->appUninstallUrl;
    }

    /**
     * Ссылка на отправку вебхука
     *
     * @return string
     */
    public function getHookUrl()
    {
        return $this->appHookUrl;
    }

    /**
     * Имя токена для автологина в сессии
     *
     * @return string
     */
    public function getSessionTokenName(string $userId): string
    {
        return self::SESSION_AUTOLOGIN_TOKEN_NAME.$userId;
    }

    /**
     * Имя флага логина в сессии
     *
     * @return string
     */
    public function getSessionLoginName(string $userId): string
    {
        return self::SESSION_LOGIN_NAME.$userId;
    }

    /**
     * Ключи параметров GET запроса по адресу APP_LOGIN_URL
     *
     * @return array
     */
    public function getGETLoginKeys()
    {
        return self::GET_LOGIN_KEYS;
    }

    /**
     * Ключи параметров GET запроса по адресу APP_AUTOLOGIN_URL
     *
     * @return array
     */
    public function getGETAutologinKeys()
    {
        return self::GET_AUTOLOGIN_KEYS;
    }
}
