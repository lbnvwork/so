<?php
declare(strict_types=1);

namespace ApiV1\Service\Umka;

use Office\Service\Umka;
use Zend\Json\Json;

/**
 * Class UmkaLkApi
 *
 * @package ApiV1\Service\Umka
 */
class UmkaLkApi extends Umka
{
    protected const PACKAGE_URL = 'kkmDocPackForFiscalization';

    /**
     * Пакетная отправка чеков
     *
     * @param array $_package
     *
     * @return array
     * @throws \Exception
     */
    public function sendPackage(array $_package): array
    {
        $t = date('Y-m-d His');
        file_put_contents(ROOT_PATH.'data/send-'.$t.'.json', Json::encode($_package).PHP_EOL);

        $data = $this->send(self::PACKAGE_URL, $_package);

        file_put_contents(ROOT_PATH.'data/send-'.$t.'.json', Json::encode($data), FILE_APPEND);

        return $data;
    }

    /**
     * @param array $_data
     *
     * @return array
     * @throws \Exception
     */
    public function getPackage(array $_data): array
    {
        $t = date('Y-m-d His');
        file_put_contents(ROOT_PATH.'data/get-'.$t.'.json', Json::encode($_data).PHP_EOL);

        $data = $this->send(self::PACKAGE_URL, $_data, ['print_info' => 'fisc']);

        file_put_contents(ROOT_PATH.'data/get-'.$t.'.json', Json::encode($data), FILE_APPEND);

        return $data;
    }

    /**
     * @param array $resultSend
     *
     * @throws \Exception
     */
    public function prepareError(array $resultSend): void
    {
        if (!empty($resultSend['error'])) {
            throw new \Exception('Ошибка отправки запроса '.$resultSend['error']);
        }

        if (empty($resultSend['results'])) {
            throw new \Exception('Запрос не обработан');
        }
    }
}
