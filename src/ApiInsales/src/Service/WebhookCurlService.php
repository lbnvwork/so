<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 18.07.19
 * Time: 16:15
 */

namespace ApiInsales\Service;

/**
 * Class WebhookCurlService
 * управление вебхуками Insales
 *
 * @package ApiInsales\Service
 */
class WebhookCurlService
{
    private $insalesSettings;

    public function __construct(
        InsalesSettingsService $insalesSettings
    ) {
        $this->insalesSettings = $insalesSettings;
    }

    /**
     * Добавляет вебхук на изменение заказа
     *
     * @param string $shop
     * @param string $password
     *
     * @return array|bool|string
     */
    public function addWebhook(string $shop, string $password)
    {
        $hookUrl = $this->insalesSettings->getHookUrl();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<webhook>
    <address>{$hookUrl}</address>
    <topic>orders/update</topic>
    <format-type>xml</format-type>
</webhook>
XML;
        $headers = [
            "Content-type: text/xml",
            "Content-length: ".strlen($xml),
            "Connection: close",
        ];
        $url = 'http://'.$shop.'/admin/webhooks.xml';
        if ($ch = curl_init()) {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, 'schetmash'.':'.$password);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


            $data = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $ret = [];
            $ret['data'] = $data;
            $ret['httpCode'] = $httpCode;
            if (curl_errno($ch)) {
                return curl_error($ch);
            }
            curl_close($ch);
            return $ret;
        }
        return false;
    }

    /**
     * Удаление вебхука
     *
     * @param $path
     * @param $password
     *
     * @return bool|string
     */
    public function deleteWebhook($path, $password)
    {
        $headers = [
            "Content-type: text/xml",
        ];

        $url = $path;
        if ($ch = curl_init()) {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, 'schetmash'.':'.$password);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            //$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (curl_errno($ch)) {
                return curl_error($ch);
            } else {
                curl_close($ch);
            }

            return $result;
        }
        return false;
    }

    /**
     * Получение установленных вебхуков
     *
     * @param $password
     * @param $shop
     *
     * @return bool|string
     */
    public function getWebhooks($password, $shop)
    {
        if ($curl = curl_init()) {
            $url = 'http://'.$shop.'/admin/webhooks.xml';
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, 'schetmash'.':'.$password);
            $out = curl_exec($curl);
            if (curl_errno($curl)) {
                return curl_error($curl);
            } else {
                curl_close($curl);
            }
            return $out;
        }
        return false;
    }
}
