<?php

namespace ApiV1Test\Service\Check;

use ApiV1\Action\Code;
use ApiV1\Service\Check\Correction;
use Doctrine\ORM\EntityManager;
use Office\Entity\Company;
use Office\Entity\Kkt;
use Office\Entity\Processing;
use Office\Entity\Shop;
use PHPUnit\Framework\TestCase;
use Zend\Json\Json;

class CorrectionTest extends TestCase
{
    /**
     * @param string $_message
     * @param array $_data
     *
     * @dataProvider correctionProvider
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testAccept(string $_message, array $_data): void
    {
        /** @var Correction $correction */
        $correction = $this->getMockBuilder(Correction::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();

        $response = $correction->accept($_data, Processing::OPERATION_SELL_CORRECTION, new Shop());
        $this->assertIsArray($response);
        $this->assertArrayHasKey('code', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals(Code::INCORECT_DATA, $response['code']);
        $this->assertStringContainsString($_message, $response['message']);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testAcceptSuccess(): void
    {
        $_data = [
            'external_id' => 1,
            'service'     => [
                'callback_url' => 'https://test.ru/callback',
            ],
            'correction'  => [
                'check'    => [
                    'inn'             => 1,
                    'payment_address' => 1,
                ],
                'info'     => [
                    'type'        => 1,
                    'date'        => 1,
                    'number'      => 1,
                    'description' => 1,
                ],
                'payments' => [
                    [
                        'type' => 1,
                        'sum'  => 1,
                    ],
                ],
                'vats'     => [
                    [
                        'type' => 1,
                        'sum'  => 1,
                    ],
                ],
            ],
        ];

        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'persist',
                    'flush',
                ]
            )
            ->getMock();

        /** @var Correction $correction */
        $correction = $this->getMockBuilder(Correction::class)
            ->setConstructorArgs([$entityManager])
            ->setMethods()
            ->getMock();

        $response = $correction->accept($_data, Processing::OPERATION_SELL_CORRECTION, new Shop());
        $this->assertIsArray($response);
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('accept', $response['status']);
    }

    public function correctionProvider(): array
    {
        return [
            [
                'external_id',
                [],
            ],
            [
                'correction',
                [
                    'external_id' => 1,
                ],
            ],
            [
                'check',
                [
                    'external_id' => 1,
                    'correction'  => [1],
                ],
            ],
            [
                'info',
                [
                    'external_id' => 1,
                    'correction'  => [
                        'check' => [1],
                    ],
                ],
            ],
            [
                'payments',
                [
                    'external_id' => 1,
                    'correction'  => [
                        'check' => [1],
                        'info'  => [1],
                    ],
                ],
            ],
            [
                'vats',
                [
                    'external_id' => 1,
                    'correction'  => [
                        'check'    => [1],
                        'info'     => [1],
                        'payments' => [1],
                    ],
                ],
            ],
            //Проверка поля check
            [
                'inn',
                [
                    'external_id' => 1,
                    'correction'  => [
                        'check'    => [1],
                        'info'     => [1],
                        'payments' => [1],
                        'vats'     => [1],
                    ],
                ],
            ],
            [
                'payment_address',
                [
                    'external_id' => 1,
                    'correction'  => [
                        'check'    => [
                            'inn' => 1,
                        ],
                        'info'     => [1],
                        'payments' => [1],
                        'vats'     => [1],
                    ],
                ],
            ],
            //Проверка info
            [
                'type',
                [
                    'external_id' => 1,
                    'correction'  => [
                        'check'    => [
                            'inn'             => 1,
                            'payment_address' => 1,
                        ],
                        'info'     => [1],
                        'payments' => [1],
                        'vats'     => [1],
                    ],
                ],
            ],
            [
                'date',
                [
                    'external_id' => 1,
                    'correction'  => [
                        'check'    => [
                            'inn'             => 1,
                            'payment_address' => 1,
                        ],
                        'info'     => ['type' => 1],
                        'payments' => [1],
                        'vats'     => [1],
                    ],
                ],
            ],
            [
                'number',
                [
                    'external_id' => 1,
                    'correction'  => [
                        'check'    => [
                            'inn'             => 1,
                            'payment_address' => 1,
                        ],
                        'info'     => [
                            'type' => 1,
                            'date' => 1,
                        ],
                        'payments' => [1],
                        'vats'     => [1],
                    ],
                ],
            ],
            [
                'description',
                [
                    'external_id' => 1,
                    'correction'  => [
                        'check'    => [
                            'inn'             => 1,
                            'payment_address' => 1,
                        ],
                        'info'     => [
                            'type'   => 1,
                            'date'   => 1,
                            'number' => 1,
                        ],
                        'payments' => [1],
                        'vats'     => [1],
                    ],
                ],
            ],
            //Проверка payments
            [
                'type',
                [
                    'external_id' => 1,
                    'correction'  => [
                        'check'    => [
                            'inn'             => 1,
                            'payment_address' => 1,
                        ],
                        'info'     => [
                            'type'        => 1,
                            'date'        => 1,
                            'number'      => 1,
                            'description' => 1,
                        ],
                        'payments' => [1],
                        'vats'     => [1],
                    ],
                ],
            ],
            [
                'sum',
                [
                    'external_id' => 1,
                    'correction'  => [
                        'check'    => [
                            'inn'             => 1,
                            'payment_address' => 1,
                        ],
                        'info'     => [
                            'type'        => 1,
                            'date'        => 1,
                            'number'      => 1,
                            'description' => 1,
                        ],
                        'payments' => [['type' => 1]],
                        'vats'     => [1],
                    ],
                ],
            ],
            //Проверка vats
            [
                'type',
                [
                    'external_id' => 1,
                    'correction'  => [
                        'check'    => [
                            'inn'             => 1,
                            'payment_address' => 1,
                        ],
                        'info'     => [
                            'type'        => 1,
                            'date'        => 1,
                            'number'      => 1,
                            'description' => 1,
                        ],
                        'payments' => [
                            [
                                'type' => 1,
                                'sum'  => 1,
                            ],
                        ],
                        'vats'     => [1],
                    ],
                ],
            ],
            [
                'sum',
                [
                    'external_id' => 1,
                    'correction'  => [
                        'check'    => [
                            'inn'             => 1,
                            'payment_address' => 1,
                        ],
                        'info'     => [
                            'type'        => 1,
                            'date'        => 1,
                            'number'      => 1,
                            'description' => 1,
                        ],
                        'payments' => [
                            [
                                'type' => 1,
                                'sum'  => 1,
                            ],
                        ],
                        'vats'     => [['type' => 1]],
                    ],
                ],
            ],
        ];
    }

    public function testPrepareSend(): void
    {
        $successResponse = [
            'document' => [
                'print'     => 0,
                'sessionId' => 'session',
                'data'      => [
                    'docName'   => 'Чек коррекции',
                    'moneyType' => 2,
                    'sum'       => 10000,
                    'type'      => 7,
                    'fiscprops' => [
                        [
                            'tag'   => 1018,
                            //ИНН
                            'value' => '123456789',
                        ],
                        [
                            'tag'   => 1187,
                            //Место расчетов
                            'value' => 'Тест',
                        ],
                        [
                            'tag'   => 1054,
                            'value' => 1,
                            //1 - приход 3 - расход
                        ],
                        [
                            'tag'   => 1055,
                            'value' => 1,
                            //система нологообложения
                        ],
                        [
                            'tag'   => 1173,
                            'value' => 1,
                            //0 -самостоятельно, 1 - по предписанию
                        ],
                        [
                            'tag'   => 1037,
                            'value' => '0000005145614',
                        ],
                        [
                            'tag'       => 1174,
                            'fiscprops' => [
                                [
                                    'tag'   => 1177,
                                    'value' => 'test',
                                ],
                                [
                                    'tag'   => 1178,
                                    'value' => '7 Jun 2017 00:00:00 +0300',
                                ],
                                [
                                    'tag'   => 1179,
                                    'value' => 'ПС10111',
                                ],
//                                [
//                                    'tag'   => 1102,
//                                    //1102-1107 Ставка НДС
//                                    'value' => 12.02,
//                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        /** @var Correction $correction */
        $correction = $this->getMockBuilder(Correction::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();

        $processing = (new Processing())
            ->setRawData(
                Json::encode(
                    [
                        'correction' => [
                            'info' => [
                                'type'        => 1,
                                'description' => 'test',
                                'date'        => '7 Jun 2017 00:00:00 +0300',
                                'number'      => 'ПС10111',
                            ],
//                            'vats' => [
//                                'type' => '',
//                                'sum'  => '',
//                            ],
                        ],
                    ]
                )
            )
            ->setSum(100)
            ->setOperation(Processing::OPERATION_SELL_CORRECTION);
        $kkt = (new Kkt())
            ->setShop(
                (new Shop())->setCompany(
                    (new Company())->setInn('123456789')
                        ->setNalogSystem(0)
                )
            )
            ->setPaymentAddress('Тест')
            ->setRegNumber('0000005145614');

        $res = $correction->prepareSend($processing, $kkt);
        $this->assertIsArray($res);
        $this->assertNotNull($res['document']['sessionId']);
        $res['document']['sessionId'] = 'session';
        $this->assertEquals($successResponse, $res);
    }
}
