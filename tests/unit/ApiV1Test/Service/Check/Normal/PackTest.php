<?php

namespace ApiV1Test\Service\Check\Normal;

use ApiV1\Service\Check\Normal\Pack;
use Office\Entity\Company;
use Office\Entity\Processing;
use Office\Entity\Shop;
use PHPUnit\Framework\TestCase;
use Zend\Json\Json;

class PackTest extends TestCase
{
    public function testPrepareSend(): void
    {
        $rawData = [
            'receipt' => [
                'attributes' => [
                    'email' => 'test@test.ru',
                ],
                'items'      => [
                    [
                        'name'     => 'test',
                        'price'    => 9,
                        'quantity' => 2,
                        'sum'      => 9 * 2,
                        'tax'      => 'none',
                    ],
                ],
                'payments'   => [
                    [
                        'type' => 1,
                        'sum'  => 10,
                    ],
                ],
            ],
        ];

        $processing = new Processing();
        $processing
            ->setShop((new Shop())->setCompany((new Company())->setInn(123456789)))
            ->setRawData(Json::encode($rawData))
            ->setDatetime(new \DateTime('2019-08-27'))
            ->setOperation(Processing::OPERATION_SELL);
        $processingSkip = (new Processing())->setOperation(Processing::OPERATION_BUY_CORRECTION);


        $class = new Pack(
            [
                'login'    => 'test',
                'password' => 'test',
            ]
        );
        $forSend = $class->prepareSend(
            [
                $processing,
                $processingSkip,
            ]
        );
        $this->assertIsArray($forSend);
        $this->assertEquals(
            [
                'docs' => [
                    [
                        'externalId'      => $processing->getSessionId(),
                        'cashierLogin'    => 'test',
                        'cashierPassword' => 'test',
                        'operationDt'     => '2019-08-27',
                        'operation'       => 'sell',
                        'companySno'      => 'osn',
                        'companyInn'      => 123456789,
                        'clientEmail'     => 'test@test.ru',
                        'clientPhone'     => null,
                        'items'           => [
                            [
                                'itemName'      => 'test',
                                'itemPrice'     => 9,
                                'itemQuantity'  => '2.000',
                                'itemSum'       => 18,
                                'paymentMethod' => 'full_payment',
                                'paymentObject' => 'commodity',
                                'vatType'       => 'none',
                            ],
                        ],
                        'payments'        => [
                            [
                                'type' => 'cashless',
                                'sum'  => 10,
                            ],
                        ],
                    ],
                ],
            ], $forSend
        );
    }

    public function testGeneratePayments()
    {
        $data = [
            'receipt' => [
                'payments' => [
                    [
                        'type' => 1,
                        'sum'  => 10,
                    ],
                ],
            ],
        ];

        $class = new Pack();
        $payments = $class->generatePayments($data);
        $this->assertIsArray($payments);
        $this->assertEquals(
            [
                [
                    'type' => 'cashless',
                    'sum'  => 10,
                ],
            ], $payments
        );
    }

    public function testGeneratePosition()
    {
        $data = [
            'receipt' => [
                'items' => [
                    [
                        'name'     => 'test',
                        'price'    => 9,
                        'quantity' => 2,
                        'sum'      => 9 * 2,
                        'tax'      => 'none',
                    ],
                ],
            ],
        ];
        $class = new Pack();
        $positions = $class->generatePosition($data);
        $this->assertIsArray($positions);
        $this->assertEquals(
            [
                [
                    'itemName'      => 'test',
                    'itemPrice'     => 9,
                    'itemQuantity'  => 2,
                    'itemSum'       => 9 * 2,
                    'vatType'       => 'none',
                    'paymentMethod' => 'full_payment',
                    'paymentObject' => 'commodity',
                ],
            ], $positions
        );
    }
}
