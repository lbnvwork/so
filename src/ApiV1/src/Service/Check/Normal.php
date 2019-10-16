<?php


namespace ApiV1\Service\Check;

use ApiV1\Action\Code;
use ApiV1\Service\Check\Normal\Pack;
use ApiV1\Service\Umka;
use App\Service\DateTime;
use App\Validator\IsFloat;
use Doctrine\ORM\EntityManager;
use Office\Entity\Kkt;
use Office\Entity\Processing;
use Office\Entity\Shop;
use Zend\Json\Json;

/**
 * Class Normal
 *
 * @package ApiV1\Service\Check
 */
class Normal
{
    private const REQUIRED_ITEM_FIELDS = [
        'name',
        'price',
        'quantity',
        'tax',
    ];
    private const VATS = [
        'none',
        'vat0',
        'vat10',
        'vat18',
        'vat20',
        'vat110',
        'vat118',
    ];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Normal constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Прием чека
     *
     * @param array $json
     * @param int $operation
     * @param Shop $shop
     *
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function accept(array $json, int $operation, Shop $shop): array
    {
        $isFloat = new IsFloat();

        $msg = null;
        if (!isset($json['external_id'])) {
            $msg = 'Не найден атрибут `external_id`';
        }

        if ($msg === null && !isset($json['receipt']['total'])) {
            $msg = 'Не найден атрибут `receipt` -> `total`';
        }

        if ($msg === null && empty($json['receipt']['items'])) {
            $msg = 'Пустой массив `items`';
        }
        if ($msg === null && (float)$json['receipt']['total'] <= 0) {
            $msg = 'Значение `total` должно быть больше 0';
        }

        if ($msg === null && !$isFloat->isValid($json['receipt']['total'])) {
            $msg = 'Значение `total` не корректно';
        }

        if ($msg === null && empty($json['receipt']['payments'])) {
            $msg = 'Пустой массив `payments`';
        }

        if ($msg !== null) {
            return [
                'code'    => Code::INCORECT_DATA,
                'message' => Code::getMessage(Code::INCORECT_DATA).', '.$msg,
            ];
        }

        $itemsSum = 0;
        $skipSumCheck = false;
        foreach ($json['receipt']['items'] as $item) {
            foreach (self::REQUIRED_ITEM_FIELDS as $key) {
                if (empty($item[$key])) {
                    return [
                        'code'    => Code::INCORECT_DATA,
                        'message' => Code::getMessage(Code::INCORECT_DATA).', не найден элемент массива receipt -> items -> '.$key,
                    ];
                }
                if ($key === 'tax' && !\in_array($item[$key], self::VATS, true)) {
                    return [
                        'code'    => Code::INCORECT_DATA,
                        'message' => Code::getMessage(Code::INCORECT_DATA).', Не верный формат данных '.$key.' - '.$item[$key],
                    ];
                }
            }
            if (!$isFloat->isValid($item['price'])) {
                return [
                    'code'    => Code::INCORECT_DATA,
                    'message' => Code::getMessage(Code::INCORECT_DATA).', Не верный формат данных price - '.$item['price'],
                ];
            }

            if (isset($item['price'])) {
                $itemsSum += $item['price'] * $item['quantity'];
            }
            if (isset($item['type']) || isset($item['mode'])) {
                $skipSumCheck = true;
            }
        }
        if ($operation === Processing::OPERATION_SELL && !$skipSumCheck) {
            if ((float)$itemsSum !== (float)$json['receipt']['total']) {
//                return new JsonResponse(
//                    [
//                        'code'    => Code::INCORECT_DATA,
//                        'message' => Code::getMessage(Code::INCORECT_DATA).', сумма товаров не равна сумме чека ('.
//                                      (float)$itemsSum.' - '.(float)$json['receipt']['total'].')'
//                    ]
//                );
            }
        }

        $processing = new Processing();
        if (isset($json['service']['callback_url'])) {
            $processing->setCallbackUrl($json['service']['callback_url']);
        }

        foreach ($json['receipt']['payments'] as $payment) {
            if (!$isFloat->isValid($payment['sum'])) {
                return [
                    'code'    => Code::INCORECT_DATA,
                    'message' => Code::getMessage(Code::INCORECT_DATA).', не корректный тип данных оплаты '.$payment['sum'],
                ];
            }
        }

        $processing->setRawData(Json::encode($json))
            ->setSum($json['receipt']['total'])
            ->setDatetime(new \DateTime())
            ->setStatus(Processing::STATUS_ACCEPT)
            ->setOperation($operation)
            ->setShop($shop)
            ->setExternalId($json['external_id']);

        $this->entityManager->persist($processing);
        $this->entityManager->flush();

        return [
            'id'     => $processing->getId(),
            'status' => 'accept',
        ];
    }

    /**
     * Подготовка чека для отправки в кассу
     *
     * @param Processing $processing
     * @param Kkt $kkt
     *
     * @return Umka\DocIn
     */
    public function prepareSend(Processing $processing, Kkt $kkt): Umka\DocIn
    {
        $typConformity = [
            Processing::OPERATION_SELL            => 1,
            Processing::OPERATION_SELL_REFUND     => 2,
            Processing::OPERATION_BUY             => 4,
            Processing::OPERATION_BUY_REFUND      => 5,
            Processing::OPERATION_SELL_CORRECTION => 7,
            Processing::OPERATION_BUY_CORRECTION  => 9,
        ];
        $shop = $processing->getShop();

        if ($processing->getSessionId() === null) {
            $processing->setSessionId(uniqid(time()));
        }

        $data = Json::decode($processing->getRawData(), Json::TYPE_ARRAY);


        $document = new Umka\DocIn();
        $document->setUserInn($shop->getCompany()->getInn());
        $ns = $shop->getCompany()->getNalogSystem();
//        if (!empty($data['receipt']['attributes']['sno'])) {
//            $ns = (int)$data['receipt']['attributes']['sno'];
//        }
        $document->setNalogSystem(pow(2, $ns));
        $document->setCalculationSign($processing->getOperation());
        $document->setType($typConformity[$processing->getOperation()]);
        $document->setSessionId($processing->getSessionId());
        $document->setDocName('Кассовый чек');
        $document->setMoneyType(2);

        $isFloat = new IsFloat(); //Для УК
        if (!$isFloat->isValid($data['receipt']['total'])) {
            $data['receipt']['total'] = (float)str_replace(' ', '', $data['receipt']['total']);
        }

        $document->setSum(round($data['receipt']['total'] * 100, 0));

        if (!empty($data['receipt']['attributes']['name'])) {
            $document->addFiscProp(new Umka\Props\BuyerName($data['receipt']['attributes']['name']));
        }
        if (!empty($data['receipt']['attributes']['inn'])) {
            $document->addFiscProp(new Umka\Props\BuyerINN($data['receipt']['attributes']['inn']));
        }

//        $document->setSum($data['receipt']['total']);
        //поле необходимо, без него будет ошибка печати чека
        $document->addFiscProp(new Umka\Props\KktNumber($kkt->getRegNumber()));
        if (!empty($data['receipt']['attributes']['email'])) {
            $document->addFiscProp(new Umka\Props\EmailOrPhoneBuyer($data['receipt']['attributes']['email']));
        } elseif (!empty($data['receipt']['attributes']['phone'])) {
            $document->addFiscProp(new Umka\Props\EmailOrPhoneBuyer($data['receipt']['attributes']['phone']));
        }

//        if (isset($data['payments'][''])) {
//            $document->addFiscProp(new Umka\Props\DopRecvezitCheck('Скидка: '.$data['payments']['']));
//        }
//        if (!empty($data['receipt']['payments'])) {
        $this->createPayments($document, $data['receipt']['payments']);
//        }

        foreach ($data['receipt']['items'] as $item) {
            $document->addFiscProp($this->addCheckItem($item));
        }

        return $document;
    }

    /**
     * Получение отчета о напечатанном чеке
     *
     * @param Processing $processing
     *
     * @return array
     * @throws \Exception
     */
    public function report(Processing $processing): array
    {
        $payload = self::createPayload($processing);

        return [
            'id'        => $processing->getId(),
            'status'    => 'success',
            'payload'   => $payload,
            'timestamp' => date('d.m.Y H:i:s'),
        ];
    }

    /**
     * @param Processing $responseData
     *
     * @return array
     * @throws \Exception
     */
    public static function createPayload(Processing $processing): array
    {
        return [
            'total'                     => $processing->getSum(),
            'fns_site'                  => 'www.nalog.ru',
            'fn_number'                 => $processing->getFnNumber(),
            'shift_number'              => $processing->getShiftNumber(),
            'receipt_datetime'          => $processing->getDatePrint()->format('d.m.Y H:i:s'),
            // date('d.m.Y H:i:s', strtotime($docOut->{'getDatetime'}())),
            'fiscal_receipt_number'     => $processing->getReceiptNumber(),
            'fiscal_document_number'    => $processing->getDocNumber(),
            'ecr_registration_number'   => $processing->getEcrRegistrationNumber(),
            'fiscal_document_attribute' => $processing->getDocumentAttribute(),
            'qr'                        => $processing->getOfdLink()
            //                    'link' => $link
        ];
    }

    /**
     * Вывод ошибки по чеку
     *
     * @param Processing $processing
     *
     * @return array
     * @throws \Exception
     */
    public function printError(Processing $processing): array
    {
        return [
            'id'        => $processing->getId(),
            'status'    => 'error',
            'payload'   => null,
            'timestamp' => (new DateTime())->format('d.m.Y H:i:s'),
            'error'     => [
                'code'    => Code::INCORECT_DATA,
                'message' => $processing->getError() ?? Code::getMessage(Code::INCORECT_DATA),
            ],
        ];
    }

    /**
     * Формирование позиции для чека
     *
     * @param array $item
     *
     * @return Umka\Props\SubjectCalculation
     */
    protected function addCheckItem(array $item): Umka\Props\SubjectCalculation
    {
        //Признак способа рассчета
        $isSposobRascheta = new Umka\FiscProp(1214, 4);
        //признак предмета расчета
        $isPredmetRascheta = new Umka\FiscProp(1212, 1);

        if (!empty($item['type'])) {
            $item['type'] = (int)$item['type'];
            $isPredmetRascheta = new Umka\FiscProp(1212, $item['type']);
        }
        if (!empty($item['mode'])) {
            $item['mode'] = (int)$item['mode'];
            $isSposobRascheta = new Umka\FiscProp(1214, $item['mode']);
        }

        //наименование предмета расчета
        $namePredmetRascheta = new Umka\FiscProp(1030, $item['name']);

        $price = $item['price'] * 100;
        //цена за единицу предмета расчета с учетом скидок и наценок
        $priceAndVat = new Umka\FiscProp(1079, $price);

        //количество предмета расчета
        $countPredmet = new Umka\FiscProp(1023, sprintf('%.03f', $item['quantity']));

        $tax = Tax::getTaxCode($item['tax']);

        //ставка НДС
        $nds = new Umka\FiscProp(1199, $tax);
        //стоимость предмета расчета с учетом скидок и наценок, не обязательно, касса рассчитает
//            $sumPredmet = new Umka\FiscProp(1043, $item['sum']);

        return new Umka\Props\SubjectCalculation(
            [
                $isSposobRascheta,
                $isPredmetRascheta,
                $namePredmetRascheta,
                $priceAndVat,
                $countPredmet,
                $nds,
                //                $sumPredmet
            ]
        );
    }

    /**
     * Формирование списка оплат
     *
     * @param Umka\DocIn $document
     * @param array $payments
     */
    protected function createPayments(Umka\DocIn $document, array $payments): void
    {
        if (count($payments) > 1) {
            foreach ($payments as $payment) {
                if ($payment['type'] == 1) {
                    $document->addFiscProp(new Umka\FiscProp(1081, $payment['sum'] * 100));
                } elseif ($payment['type'] == 2) {
                    $document->addFiscProp(new Umka\FiscProp(1215, $payment['sum'] * 100));
//                    $document->addFiscProp(new Umka\FiscProp(1218, $payment['sum'] * 100));
                } elseif ($payment['type'] == 3) {
                    $document->addFiscProp(new Umka\FiscProp(1216, $payment['sum'] * 100));
//                    $document->addFiscProp(new Umka\FiscProp(1219, $payment['sum'] * 100));
                }
            }
        }
    }
}
