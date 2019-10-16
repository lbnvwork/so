<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 30.01.18
 * Time: 21:33
 */

namespace ApiV1\Service\Umka;

use ApiV1\Service\Umka\Exception\TimeoutException;
use Zend\Json\Json;

/**
 * Class UmkaApi
 */
class UmkaApi
{
    /**
     * Url получения состояния кассы
     */
    const CASHBOX_STATUS = 'cashboxstatus.json';
    /**
     * Url получение документа из фискального накопителя
     */
    const FISCAL_DOC = 'fiscaldoc.json';
    /**
     * Открытие смены
     */
    const CYCLEOPEN = 'cycleopen.json';
    /**
     * Закрытие смены
     */
    const CYCLECLOSE = 'cycleclose.json';
    /**
     * Получение отчета о состояние расчетов
     */
    const CALCREPORT = 'calcreport.json';
    /**
     * Печать чека
     */
    const FISCALCHECK = 'fiscalcheck.json';
    /**
     * Закрытие ФН
     */
    public const CLOSE_FN = 'closefs.json';
    /**
     * ID клиента в сервисе
     */
    public const CLIENT_ID = 1888;

    /**
     * Хост
     *
     * @var string
     */
    protected $host;

    /**
     * Логин
     *
     * @var string
     */
    protected $username;

    /**
     * Пароль
     *
     * @var string
     */
    protected $password;

    /**
     * HTTP код последнего запроса
     *
     * @var int|null
     */
    protected $lastHttpCode;

    /** @var array */
    protected $lastResponse;

    /**
     * UmkaApi constructor.
     *
     * @param null|array $_config
     *
     * @throws \Exception
     */
    public function __construct($_config = null)
    {
        if (!function_exists('curl_version')) {
            throw new \Exception('Curl not installed!');
        }

        if (is_array($_config)) {
            foreach ($_config as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Установка хоста
     *
     * @param string $_host
     *
     * @return $this
     */
    public function setHost($_host)
    {
        $this->host = $_host;

        return $this;
    }

    /**
     * Установка логина
     *
     * @param string $_username
     *
     * @return $this
     */
    public function setUsername($_username)
    {
        $this->username = $_username;

        return $this;
    }

    /**
     * Установка пароля
     *
     * @param string $_password
     *
     * @return $this
     */
    public function setPassword($_password)
    {
        $this->password = $_password;

        return $this;
    }

    /**
     * Получение состояния кассы
     *
     * @param string $serial
     *
     * @return mixed
     * @throws \Exception
     */
    public function getCashboxStatus(string $serial)
    {
        $query = [
            'frmodel'  => 200,
            'frserial' => $serial,
        ];

        return $this->jsonToArray($this->send(self::CASHBOX_STATUS, [], null, $query))['cashboxStatus'];
    }

    /**
     * Получение документа из фискального накопителя
     *
     * @param $_number
     * @param bool $_isPrint
     *
     * @return array
     * @throws \Exception
     */
    public function getFiscalDoc($_number, $_isPrint = false)
    {
        return $this->jsonToArray(
            $this->send(
                self::FISCAL_DOC,
                [
                    'number' => $_number,
                    'print'  => $_isPrint,
                ]
            )
        );
    }

    /**
     * Открытие смены
     *
     * @param string $serial
     * @param bool $isPrint
     *
     * @return array
     * @throws \Exception
     */
    public function cycleOpen(string $serial, bool $isPrint = true): array
    {
        $query = [
            'print'    => $isPrint,
            'frmodel'  => 200,
            'frserial' => $serial,
        ];

        return $this->jsonToArray($this->send(self::CYCLEOPEN, [], null, $query));
    }

    /**
     * Закрытие смены
     *
     * @param bool $isPrint
     *
     * @return array
     * @throws \Exception
     */
    public function cycleClose(string $serial, bool $isPrint = true)
    {
        $query = [
            'print'    => $isPrint,
            'frmodel'  => 200,
            'frserial' => $serial,
        ];

        return $this->jsonToArray($this->send(self::CYCLECLOSE, [], null, $query));
    }

    /**
     * Получение отчета о состояние расчетов
     *
     * @param bool $isPrint
     *
     * @return array
     * @throws \Exception
     */
    public function calcReport($isPrint = false)
    {
        return $this->jsonToArray($this->send(self::CALCREPORT, ['print' => $isPrint]));
    }

    /**
     * Печать чека
     *
     * @param array|DocIn $_data
     * @param string $inn
     *
     * @return array
     * @throws TimeoutException
     */
    public function fiscalCheck($_data, string $inn)
    {
        if ($_data instanceof DocIn) {
            $_data = $_data->toArray();
            file_put_contents(ROOT_PATH.'data/file.json', Json::encode($_data));
        }

        return $this->jsonToArray($this->send(self::FISCALCHECK, $_data, $inn));
    }

    /**
     * @param string $serial
     * @param string $inn
     * @param bool $isPrint
     *
     * @return array
     * @throws TimeoutException
     */
    public function closeFn(string $serial, string $inn, bool $isPrint = true): array
    {
        $query = [
            'print'    => $isPrint,
            'frmodel'  => 200,
            'frserial' => $serial,
        ];

        return $this->jsonToArray($this->send(self::CLOSE_FN, [], $inn, $query));
    }

    /**
     * Преобразование json строки в массив
     *
     * @param string $_json
     *
     * @return array
     */
    protected function jsonToArray(string $_json): array
    {
        return Json::decode($_json, Json::TYPE_ARRAY);
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * @param string $_path
     * @param array $_data
     * @param string|null $inn
     * @param array|null $_query
     *
     * @return bool|string
     * @throws TimeoutException
     */
    public function send(string $_path, array $_data, string $inn = null, array $_query = null)
    {
        if ($this->host === null) {
            throw new \InvalidArgumentException('Host not set!');
        }

        if ($this->username === null) {
            throw new \InvalidArgumentException('Username not set!');
        }

        if ($this->password === null) {
            throw new \InvalidArgumentException('Password not set!');
        }

        $query = ['clientId' => self::CLIENT_ID];
        if ($inn !== null) {
            $query['inn'] = $inn;
        }

        if ($_query) {
            $query = array_merge($query, $_query);
        }

        $_path .= '?'.http_build_query($query);

        $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
        #Устанавливаем необходимые опции для сеанса cURL

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $this->host.'/'.$_path);
        if (!empty($_data)) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($_data));
        }
        curl_setopt($curl, CURLOPT_HEADER, false);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_USERPWD, $this->username.":".$this->password);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
        $err = curl_error($curl);
        curl_close($curl); #Завершаем сеанс cURL
//var_dump($out, $code);
        $code = (int)$code;
        $errors = [
            301 => 'Moved permanently',
            400 => 'Bad request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not found',
            451 => 'Unavailable For Legal Reasons',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable',
        ];

        $this->setLastHttpCode($code);
        $this->lastResponse = $out;

        if (!$code) {
            return $err;
        }

        if ($code === 524) {
            throw new TimeoutException();
        }

        if ($code !== 200) {
            throw new \Exception($errors[$code]);
        }

        return $out;
    }

    /**
     * @return int|null
     */
    public function getLastHttpCode(): ?int
    {
        return $this->lastHttpCode;
    }

    /**
     * @param int|null $lastHttpCode
     */
    public function setLastHttpCode(?int $lastHttpCode): void
    {
        $this->lastHttpCode = $lastHttpCode;
    }
}
