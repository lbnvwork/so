<?php
declare(strict_types=1);

namespace ApiV1\Service\Umka\CloseFn;

use ApiV1\Service\Umka\AbstractResponse;
use ApiV1\Service\Umka\ExtendFiscProp;

/**
 * Отчет о закрытии ФН
 * Class Response
 *
 * @package ApiV1\Service\Umka\Correction
 */
class Response extends AbstractResponse
{
    /**
     *  Тип чека о закрытии ФН
     */
    public const TYPE = 6;
    /**
     * Соотвествие тегов - классам свойств
     */
    private const PROPS = [
        1009 => 'PaymentAddress',
        1012 => 'Datetime',
        1018 => 'UserInn',
        1021 => 'Cashier',
        1037 => 'KktNumber',
        1038 => 'ShiftNumber',
        1040 => 'FD',
        1041 => 'FN',
        1042 => 'ReceiptNumber',
        1048 => 'OwnerName',
        1054 => 'CalculationSign',
        1077 => 'FPD',
        1081 => 'ReceiptSumElectro',
        1187 => 'PaymentPlace',
    ];

    /**
     * @var array
     */
    private $document;

    /**
     * @var array
     */
    private $fiscProps;

    /**
     * Response constructor.
     *
     * @param array $document
     *
     * @throws InvalidDocumentException
     */
    public function __construct(array $document)
    {
        if (empty($document['document']['docType']) || $document['document']['docType'] !== self::TYPE) {
            throw new InvalidDocumentException('Не корректный тип документа');
        }

        $this->document = $document['document'];

        foreach ($document['document']['fiscprops'] as $prop) {
            if (isset(self::PROPS[$prop['tag']])) {
                $name = '\ApiV1\Service\Umka\Props\\'.self::PROPS[$prop['tag']];
                $this->fiscProps[] = new $name($prop['value']);
            }
        }
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return $this|mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        $type = substr($name, 0, 3);
        $propName = substr($name, 3);

        if ($type === 'get') {
            if (property_exists($this, lcfirst($propName))) {
                return $this->{lcfirst($propName)};
            }

            $class = 'ApiV1\Service\Umka\Props\\'.$propName;

            /** @var ExtendFiscProp $prop */
            foreach ($this->fiscProps as $prop) {
                if ($prop instanceof $class) {
                    return $prop->getValue();
                }
            }
        }

        throw new \Exception('Call to undefined method '.$name);
    }

    /**
     * Текстовая печать
     *
     * @return string
     */
    public function print(): string
    {
        $text = [];

        $text[] = 'Адрес расчётов: '.$this->getPaymentAddress();
        $text[] = 'Версия ФФД: 2';
        $text[] = 'Дата, время: '.$this->getDatetime();
        $text[] = 'ИНН владельца: '.$this->getUserInn();
        $text[] = 'ИНН кассира: '.$this->getUserInn();
        $text[] = 'Кассир: '.$this->getCashier();
        $text[] = 'Место расчетов: '.$this->getPaymentPlace();
        $text[] = 'Наименование пользователя: '.$this->getOwnerName();
        $text[] = 'Номер ФД: '.$this->getFD();
        $text[] = 'Номер ФН: '.$this->getFN();
        $text[] = 'Регистрационный номер ККТ: '.$this->getKktNumber();
        $text[] = 'Смена: '.$this->getShiftNumber();
        $text[] = 'Фискальный признак документа: '.$this->getFPD();

        return implode(PHP_EOL, $text);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->print();
    }
}
