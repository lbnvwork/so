<?php
declare(strict_types=1);

namespace ApiV1\Service\Check;

use ApiV1\Action\Code;
use Doctrine\ORM\EntityManager;
use Office\Entity\Kkt;
use Office\Entity\Processing;
use Office\Entity\Shop;
use Zend\Json\Json;

/**
 * Class Correction
 *
 * @package ApiV1\Service\Check
 */
class Correction
{
    public const ROW_CHECK = [
        'inn',
        'payment_address',
    ];
    public const ROW_INFO = [
        'type',
        'date',
        'number',
        'description',
    ];
    public const ROW_PAYMENT = [
        'type',
        'sum',
    ];
    public const ROW_VAT = [
        'type',
        'sum',
    ];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Correction constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
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
        $msg = null;
        if (!isset($json['external_id'])) {
            $msg = 'Не найден атрибут `external_id`';
        }

        if ($msg === null && empty($json['correction'])) {
            $msg = 'Не найден атрибут `correction`';
        }

        if ($msg === null && empty($json['correction']['check'])) {
            $msg = 'Не найден атрибут `correction -> check`';
        }

        if ($msg === null && empty($json['correction']['info'])) {
            $msg = 'Не найден атрибут `correction -> info`';
        }

        if ($msg === null && empty($json['correction']['payments'])) {
            $msg = 'Не найден атрибут `correction -> payments`';
        }

        if ($msg === null && empty($json['correction']['vats'])) {
            $msg = 'Не найден атрибут `correction -> vats`';
        }

        if ($msg !== null) {
            return [
                'code'    => Code::INCORECT_DATA,
                'message' => Code::getMessage(Code::INCORECT_DATA).', '.$msg,
            ];
        }

        foreach (self::ROW_CHECK as $item) {
            if (empty($json['correction']['check'][$item])) {
                $msg = 'Не найден элемент массива correction -> check -> '.$item;
                break;
            }
        }

        if ($msg !== null) {
            return [
                'code'    => Code::INCORECT_DATA,
                'message' => Code::getMessage(Code::INCORECT_DATA).', '.$msg,
            ];
        }

        foreach (self::ROW_INFO as $item) {
            if (empty($json['correction']['info'][$item])) {
                $msg = 'Не найден элемент массива correction -> info -> '.$item;
                break;
            }
        }

        if ($msg !== null) {
            return [
                'code'    => Code::INCORECT_DATA,
                'message' => Code::getMessage(Code::INCORECT_DATA).', '.$msg,
            ];
        }

        $sum = 0;
        foreach ($json['correction']['payments'] as $payment) {
            foreach (self::ROW_PAYMENT as $item) {
                if (empty($payment[$item])) {
                    $msg = 'Не найден элемент массива correction -> payments -> '.$item;
                    break 2;
                }
            }
            $sum += $payment['sum'];
        }

        if ($msg !== null) {
            return [
                'code'    => Code::INCORECT_DATA,
                'message' => Code::getMessage(Code::INCORECT_DATA).', '.$msg,
            ];
        }

        foreach ($json['correction']['vats'] as $vat) {
            foreach (self::ROW_VAT as $item) {
                if (empty($vat[$item])) {
                    $msg = 'Не найден элемент массива correction -> vats -> '.$item;
                    break 2;
                }
            }
        }

        if ($msg !== null) {
            return [
                'code'    => Code::INCORECT_DATA,
                'message' => Code::getMessage(Code::INCORECT_DATA).', '.$msg,
            ];
        }

        $processing = new Processing();
        if (isset($json['service']['callback_url'])) {
            $processing->setCallbackUrl($json['service']['callback_url']);
        }

        $processing->setRawData(Json::encode($json))
            ->setSum($sum)
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
     * @param Processing $processing
     * @param Kkt $kkt
     *
     * @return array
     */
    public function prepareSend(Processing $processing, Kkt $kkt): array
    {
        if ($processing->getSessionId() === null) {
            $processing->setSessionId(uniqid((string)time()));
        }

        $data = Json::decode($processing->getRawData(), Json::TYPE_ARRAY);

        $operation = $processing->getOperation() === Processing::OPERATION_SELL_CORRECTION
            ? Processing::OPERATION_SELL : Processing::OPERATION_BUY;
        $ns = $kkt->getShop()->getCompany()->getNalogSystem();

        $vats = [];
//        foreach ($data['vats'] as $vat) {
//            $vats[] = [
//
//            ];
//        }

        $document = [
            'document' => [
                'print'     => 0,
                'sessionId' => $processing->getSessionId(),
                'data'      => [
                    'docName'   => 'Чек коррекции',
                    'moneyType' => 2,
                    'sum'       => (int)round($processing->getSum() * 100, 0),
                    'type'      => 7,
                    'fiscprops' => [
                        [
                            'tag'   => 1018,
                            'value' => $kkt->getShop()->getCompany()->getInn(),
                        ],
                        [
                            'tag'   => 1187,
                            'value' => $kkt->getPaymentAddress(),
                        ],
                        [
                            'tag'   => 1054,
                            'value' => $operation,
                        ],
                        [
                            'tag'   => 1055,
                            'value' => pow(2, $ns),
                        ],
                        [
                            'tag'   => 1173,
                            'value' => $data['correction']['info']['type'],
                            //0 -самостоятельно, 1 - по предписанию
                        ],
                        [
                            'tag'   => 1037,
                            'value' => $kkt->getRegNumber(),
                        ],
                        [
                            'tag'       => 1174,
                            'fiscprops' => [
                                [
                                    'tag'   => 1177,
                                    'value' => $data['correction']['info']['description'],
                                ],
                                [
                                    'tag'   => 1178,
                                    'value' => $data['correction']['info']['date'],
                                ],
                                [
                                    'tag'   => 1179,
                                    'value' => $data['correction']['info']['number'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $document;
    }
}
