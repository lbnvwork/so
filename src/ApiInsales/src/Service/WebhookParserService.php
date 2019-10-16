<?php
/**
 * Created by PhpStorm.
 * User: m-lobanov
 * Date: 12.08.19
 * Time: 14:13
 */

namespace ApiInsales\Service;

use Symfony\Component\VarDumper\VarDumper;

/**
 * Class WebhookParserService
 * Вытаскивает значения xml-заказ из вебхука
 *
 * @package ApiInsales\Service
 */
class WebhookParserService
{
    private $allowedNullItems;
    //private $retArr;
    private $preparedArr;
    /**
     * @var \DOMDocument
     */
    private $order;

    public function __construct()
    {
        //данные с разрешенными null значениями
        $this->allowedNullItems = [
            [
                'key'    => 'callback_url',
                'parent' => 'service',
            ],
            [
                'key'    => 'email',
                'parent' => 'attributes',
            ],
            [
                'key'    => 'phone',
                'parent' => 'attributes',
            ],
            [
                'key'    => 'name',
                'parent' => 'attributes',
            ],
            [
                'key'    => 'inn',
                'parent' => 'attributes',
            ],
        ];
    }

    /**
     * Получение DOM дерева, к-е будет использоваться всеми методами
     *
     * @param string $xml
     *
     * @return null
     */
    public function loadXml(string $xml)
    {
        if (!$xml) {
            return null;
        }
        $this->order = new \DOMDocument();
        $this->order->validateOnParse = true;
        $this->order->loadXML($xml);
    }

    /**
     * Возвращает insalesId
     *
     * @param $xml
     *
     * @return string|null
     */
    public function getInsalesId()
    {
        return $this->order ? $this->getElementByTagName($this->order, 'account-id') : null;
    }

    /**
     * Возвращает true, если последнее изменение заказа было на "оплачено"
     *
     * @param string $xml
     *
     * @return bool
     */
    public function isStatusPaid()
    {
        $status = $this->order ? $this->getElementByTagName($this->order, 'value-is', 'order-change') : null;
        if ($status === 'paid') {
            return true;
        }
        return false;
    }

    /**
     * Получение массива для отправки чека из xml заказа insales
     *
     * @param string $xml
     *
     * @return null
     */
    public function getOrderArr()
    {
        if ($this->order) {
            $retArr['timestamp'] = $this->getElementByTagName($this->order, 'created-at');
            $retArr['external_id'] = $this->getElementByTagName($this->order, 'id');
            $retArr['receipt']['attributes']['email'] = $this->getElementByTagName($this->order, 'email', 'client');
            $retArr['receipt']['attributes']['phone'] = $this->getElementByTagName($this->order, 'phone', 'client');
            $retArr['receipt']['attributes']['name'] = $this->getElementByTagName($this->order, 'name', 'client');
            $orderLines = $this->order->getElementsByTagName('order-line');
            /** @var \DOMElement $orderLine */
            foreach ($orderLines as $orderLine) {
                $retArr['receipt']['items'][] = [
                    //'name'  => null,
                    'name'     => $this->getElementByTagName($orderLine, 'title'),
                    'price'    => $this->getElementByTagName($orderLine, 'full-sale-price'),
                    'quantity' => $this->getElementByTagName($orderLine, 'quantity'),
                    'sum'      => $this->getElementByTagName($orderLine, 'full-total-price'),
                    "tax"      => "vat20",
                    "tax_sum"  => 2.59,
                ];
            }
            $fullDeliveryPrice = $this->getElementByTagName($this->order, 'full-delivery-price');
            if ($fullDeliveryPrice) {
                $retArr['receipt']['items'][] = [
                    'name'     => 'Доставка',
                    'price'    => $fullDeliveryPrice,
                    'quantity' => '1',
                    'sum'      => $fullDeliveryPrice,
                    "tax"      => "vat20",
                    "tax_sum"  => 2.59,
                ];
            }
            $retArr['receipt']['total'] = $this->getElementByTagName($this->order, 'total-price');
            $retArr['receipt']['payments'][0]['sum'] = $retArr['receipt']['total'];
            return $this->testData($retArr) ? $retArr : null;
        } else {
            return null;
        }
    }

    /**
     * Получение элемента по назанию тега
     *
     * @param \DOMNode $node
     * @param string $nodeName
     * @param string|null $parentNodeName
     *
     * @return string|null
     */
    private function getElementByTagName(\DOMNode $node, string $nodeName, string $parentNodeName = null)
    {
        if (!trim($parentNodeName)) {
            $parentNodeName = $node->nodeName;
            if ($parentNodeName == '#document') {
                $parentNodeName = 'order';
            }
        }
        /** @var \DOMNodeList $elementsColl */
        $elementsColl = $node->getElementsByTagName($nodeName);
        if ($elementsColl->count() < 1) {
            return null;
        }
        /** @var \DOMElement $element */
        foreach ($elementsColl as $element) {
            if ($element->parentNode->nodeName === $parentNodeName) {
                if (empty(trim($element->nodeValue))) {
                    return null;
                }
                return $element->nodeValue;
            }
        }
        return null;
    }

    /**
     * Проверка данных в массиве
     *
     * @param array $retArr
     *
     * @return bool
     */
    private function testData(array $retArr)
    {
        $this->prepareTestArr($retArr, null);
        foreach ($this->preparedArr as $retElement) {
            foreach ($this->allowedNullItems as $item) {
                if ($item['key'] == $retElement['key'] && $item['parent'] == $retElement['parent']) {
                    continue 2;
                }
            }
            if ($retElement['value'] == null) {
                return false;
            }
        }
        return true;
    }

    /**
     * Подготовка промежуточного массива для проверки данных
     *
     * @param array $arrForPrepare
     * @param $parentKey
     */
    private function prepareTestArr(array $arrForPrepare, $parentKey)
    {
        foreach ($arrForPrepare as $key => $value) {
            if (is_array($value)) {
                $parentKey = $key;
                $this->prepareTestArr($value, $parentKey);
            } else {
                $this->preparedArr[] = [
                    'key'    => $key,
                    'value'  => $value,
                    'parent' => $parentKey,
                ];
            }
        }
    }
}
