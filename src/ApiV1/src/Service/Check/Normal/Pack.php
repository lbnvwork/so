<?php
declare(strict_types=1);

namespace ApiV1\Service\Check\Normal;

use ApiV1\Middleware\CheckRequestMiddleware;
use Office\Entity\Processing;
use Zend\Json\Json;

/**
 * Class Pack
 *
 * @package ApiV1\Service\Check\Normal
 */
class Pack
{
    private const PAYMENTS = [
        // - наличными
        0 => 'cash',
        // - безналом
        1 => 'cashless',
        // - предоплатой
        2 => 'advance',
        // - в кредит
        3 => 'credit',
    ];
    private const PAYMENT_METHOD = [
        // - Предоплата 100%
        1 => 'full_prepayment',
        // - Предоплата
        'prepayment',
        // - Аванс
        'advance',
        // - Полный расчет
        'full_payment',
        // - Частичный расчет и кредит
        'partial_payment',
        // - Передача в кредит
        'credit',
        // - Оплата кредита
        'credit_payment',
    ];
    private const PAYMENT_OBJECT = [
        // - Товар
        1 => 'commodity',
        // - Подакцизный товар
        'excise',
        // - Работа
        'job',
        // - Услуга
        'service',
        // - Ставка азартной игры
        'gambling_bet',
        // - Выигрыш азартной игры
        'gambling_prize',
        // - Ставка (билет) лотереи
        'lottery',
        // - Выигрыш лотереи
        'lottery_prize',
        // - Предоставление результатов интеллектуальной деятельности
        'intellectual_activity',
        // - Аванс/задаток/предоплата/кредит/взнос/пеня/штраф/вознаграждение/бонус
        'payment',
        // - Агентское вознаграждение
        'agent_commission',
        // - Составной предмет расчета
        'composite',
        //  - Иной предмет расчёта
        'another',
        // - Имущественное право
        'property_right',
        // - Внереализационный доход
        'non_operating_gain',
        // - Страховые взносы
        'insurance_premium',
        // - Торговый сбор
        'sales_tax',
        // - Курортный сбор
        'resort_fee',
    ];
    /**
     * Система налогообложения
     */
    private const SNO = [
        0 => 'osn',
        'usn_income',
        'usn_income_outcome',
        'envd',
        'esn',
        'patent',
    ];

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $password;

    /**
     * @var bool Флаг включения тестового режима
     */
    private $isTest = false;

    /**
     * Pack constructor.
     *
     * @param array|null $_config
     */
    public function __construct(array $_config = null)
    {
        if (is_array($_config)) {
            foreach ($_config as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Подготовка данных для отправки чеков
     *
     * @param Processing[] $_processings
     *
     * @return array
     */
    public function prepareSend(array $_processings): array
    {
        $data = ['docs' => []];
        $correctionType = [
            Processing::OPERATION_SELL_CORRECTION,
            Processing::OPERATION_BUY_CORRECTION,
        ];

        foreach ($_processings as $processing) {
            if (in_array($processing->getOperation(), $correctionType, true)) {
                continue;
            }
            $data['docs'][] = $this->generateItem($processing);
        }

        return $data;
    }

    /**
     * Подготовка данных для получения чеков
     *
     * @param Processing[] $processing
     *
     * @return array
     */
    public function prepareGet(array $processing): array
    {
        $data = ['items' => []];

        foreach ($processing as $item) {
            $company = $item->getShop()->getCompany();

            $data['items'][] = [
                'externalId'      => $item->getSessionId(),
                'cashierLogin'    => $this->isTest() ? 'test' : $this->getLogin(),
                'cashierPassword' => $this->isTest() ? 'test' : $this->getPassword(),
                'companyInn'      => $this->isTest() ? 'test' : $company->getInn(),
            ];
        }

        return $data;
    }

    /**
     * Генерация 1 чека
     *
     * @param Processing $processing
     *
     * @return array
     */
    public function generateItem(Processing $processing): array
    {
        $checkData = Json::decode($processing->getRawData(), Json::TYPE_ARRAY);
        $company = $processing->getShop()->getCompany();
        $processing->setSessionId(uniqid((string)time()))
            ->setStatus(200);

        $data = [
            'externalId'      => $processing->getSessionId(),
            'cashierLogin'    => $this->isTest() ? 'test' : $this->getLogin(),
            'cashierPassword' => $this->isTest() ? 'test' : $this->getPassword(),
            'operationDt'     => $processing->getDatetime()->format('Y-m-d'),
            'operation'       => CheckRequestMiddleware::ALLOWED_OPERATION[$processing->getOperation()],
//            'companyEmail'    => $company->getCompanyEmail(),
            'companySno'      => self::SNO[$company->getNalogSystem()],
            'companyInn'      => $this->isTest() ? 'test' : $company->getInn(),
//            'paymentAddress'  => ,
            'clientEmail'     => $checkData['receipt']['attributes']['email'] ?? null,
            'clientPhone'     => $checkData['receipt']['attributes']['phone'] ?? null,
            'items'           => $this->generatePosition($checkData),
            'payments'        => $this->generatePayments($checkData),
        ];

        return $data;
    }

    /**
     * Генерация позиций чека
     *
     * @param array $checkData
     *
     * @return array
     */
    public function generatePosition(array $checkData): array
    {
        $positions = [];
        foreach ($checkData['receipt']['items'] as $item) {
            $positions[] = [
                'itemName'      => $item['name'],
                'itemPrice'     => $item['price'],
                'itemQuantity'  => sprintf('%.03f', $item['quantity']),
                'itemSum'       => $item['sum'],
//                'measurementUnit' => 'шт',
                'paymentMethod' => !empty($item['mode']) && self::PAYMENT_METHOD[$item['mode']] ? self::PAYMENT_METHOD[$item['mode']] : self::PAYMENT_METHOD[4],
                'paymentObject' => !empty($item['type']) && self::PAYMENT_OBJECT[$item['type']] ? self::PAYMENT_OBJECT[$item['type']] : self::PAYMENT_OBJECT[1],
                'vatType'       => $item['tax'],
            ];
        }

        return $positions;
    }

    /**
     * Генерация оплат по чеку
     *
     * @param array $checkData
     *
     * @return array
     */
    public function generatePayments(array $checkData): array
    {
        $payments = [];
        foreach ($checkData['receipt']['payments'] as $item) {
            $payments[] = [
                'type' => self::PAYMENTS[$item['type']],
                'sum'  => str_replace(chr(194).chr(160), '', $item['sum']),
            ];
        }

        return $payments;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return $this;
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * @param string $login
     *
     * @return $this;
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @param bool|null $isTest
     *
     * @return bool
     */
    public function isTest(bool $isTest = null): bool
    {
        if ($isTest !== null) {
            $this->isTest = $isTest;
        }

        return $this->isTest;
    }

    /**
     * @param array $responseData
     *
     * @return array
     */
    public static function createReportPayload(array $responseData): array
    {
        return [
            'total'                     => $responseData['fisc']['total'],
            'fns_site'                  => 'www.nalog.ru',
            'fn_number'                 => $responseData['fisc']['fnNumber'],
            'shift_number'              => $responseData['fisc']['shiftNumber'],
            'receipt_datetime'          => $responseData['fisc']['receiptDatetime'],
            //date('d.m.Y H:i:s', strtotime($docOut->{'getDatetime'}())),
            'fiscal_receipt_number'     => $responseData['fisc']['fiscalReceiptNumber'],
            'fiscal_document_number'    => $responseData['fisc']['fiscalDocumentNumber'],
            'ecr_registration_number'   => $responseData['fisc']['ecrRegistrationNumber'],
            'fiscal_document_attribute' => $responseData['fisc']['fiscalDocumentAttribute'],
            'qr'                        => $responseData['fisc']['QR'],
        ];
    }
}
