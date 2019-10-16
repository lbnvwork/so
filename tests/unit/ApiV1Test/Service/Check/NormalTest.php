<?php

namespace ApiV1Test\Service\Check;

use ApiV1\Action\Code;
use ApiV1\Service\Check\Normal;
use ApiV1\Service\Umka\DocIn;
use ApiV1\Service\Umka\Props\SubjectCalculation;
use Doctrine\ORM\EntityManager;
use Office\Entity\Company;
use Office\Entity\Kkt;
use Office\Entity\Processing;
use Office\Entity\Shop;
use PHPUnit\Framework\TestCase;
use Zend\Json\Json;

class NormalTest extends TestCase
{
    /**
     * @param $_code
     * @param $_message
     * @param $_data
     *
     * @dataProvider receiptProvider
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testAccept($_code, $_message, $_data): void
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $action = new Normal($entityManager);

        $response = $action->accept($_data, Processing::OPERATION_SELL, new Shop());
        $this->assertIsArray($response);
        $this->assertArrayHasKey('code', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals($_code, $response['code']);
        $this->assertStringContainsString($_message, $response['message']);
    }

    public function receiptProvider(): array
    {
        return [
            'Not found total'    => [
                Code::INCORECT_DATA,
                'Не найден атрибут `receipt` -> `total`',
                [
                    'external_id' => 1,
                    'receipt'     => [],
                ],
            ],
            'Empty items'        => [
                Code::INCORECT_DATA,
                'Пустой массив `items`',
                [
                    'external_id' => 1,
                    'receipt'     => [
                        'total' => 0,
                    ],
                ],
            ],
            'Incorrect total 0'  => [
                Code::INCORECT_DATA,
                'Значение `total` должно быть больше 0',
                [
                    'external_id' => 1,
                    'receipt'     => [
                        'total' => 0,
                        'items' => [
                            0,
                        ],
                    ],
                ],
            ],
            'Incorrect total'    => [
                Code::INCORECT_DATA,
                'Значение `total` не корректно',
                [
                    'external_id' => 1,
                    'receipt'     => [
                        'total' => '1 123.02',
                        'items' => [
                            0,
                        ],
                    ],
                ],
            ],
            'Empty payments'     => [
                Code::INCORECT_DATA,
                'Пустой массив `payments`',
                [
                    'external_id' => 1,
                    'receipt'     => [
                        'total' => '1123.02',
                        'items' => [
                            0,
                        ],
                    ],
                ],
            ],
            [
                Code::INCORECT_DATA,
                'name',
                [
                    'external_id' => 1,
                    'receipt'     => [
                        'total'    => 10,
                        'items'    => [
                            [
                                'test' => 'test',
                            ],
                        ],
                        'payments' => [
                            [
                                'type' => 1,
                                'sum'  => '1 111.01',
                            ],
                        ],
                    ],
                ],
            ],
            'Empty name'         => [
                Code::INCORECT_DATA,
                'name',
                [
                    'external_id' => 1,
                    'receipt'     => [
                        'total'    => 10,
                        'items'    => [
                            [
                                'name' => '',
                            ],
                        ],
                        'payments' => [
                            [
                                'type' => 1,
                                'sum'  => '1 111.01',
                            ],
                        ],
                    ],
                ],
            ],
            [
                Code::INCORECT_DATA,
                'price',
                [
                    'external_id' => 1,
                    'receipt'     => [
                        'total'    => 10,
                        'items'    => [
                            [
                                'name' => 'test',
                            ],
                        ],
                        'payments' => [
                            [
                                'type' => 1,
                                'sum'  => '1 111.01',
                            ],
                        ],
                    ],
                ],
            ],
            [
                Code::INCORECT_DATA,
                'quantity',
                [
                    'external_id' => 1,
                    'receipt'     => [
                        'total'    => 10,
                        'items'    => [
                            [
                                'name'  => 'test',
                                'price' => 'test',
                            ],
                        ],
                        'payments' => [
                            [
                                'type' => 1,
                                'sum'  => '1 111.01',
                            ],
                        ],
                    ],
                ],
            ],
            [
                Code::INCORECT_DATA,
                'tax',
                [
                    'external_id' => 1,
                    'receipt'     => [
                        'total'    => 10,
                        'items'    => [
                            [
                                'name'     => 'test',
                                'price'    => 'test',
                                'quantity' => 'test',
                            ],
                        ],
                        'payments' => [
                            [
                                'type' => 1,
                                'sum'  => '1 111.01',
                            ],
                        ],
                    ],
                ],
            ],
            [
                Code::INCORECT_DATA,
                'Не верный формат данных',
                [
                    'external_id' => 1,
                    'receipt'     => [
                        'total'    => 10,
                        'items'    => [
                            [
                                'name'     => 'test',
                                'price'    => 10,
                                'quantity' => 2,
                                'tax'      => 'test',
                            ],
                        ],
                        'payments' => [
                            [
                                'type' => 1,
                                'sum'  => '1 111.01',
                            ],
                        ],
                    ],
                ],
            ],
            [
                Code::INCORECT_DATA,
                'Не верный формат данных',
                [
                    'external_id' => 1,
                    'receipt'     => [
                        'total'    => 10,
                        'items'    => [
                            [
                                'name'     => 'test',
                                'price'    => '10,2',
                                'quantity' => 2,
                                'tax'      => 'none',
                            ],
                        ],
                        'payments' => [
                            [
                                'type' => 1,
                                'sum'  => '1 111.01',
                            ],
                        ],
                    ],
                ],
            ],
            'Incorrect payments' => [
                Code::INCORECT_DATA,
                'не корректный тип данных оплаты',
                [
                    'external_id' => 1,
                    'receipt'     => [
                        'total'    => 10,
                        'items'    => [
                            [
                                'name'     => 'test',
                                'price'    => 10,
                                'quantity' => 2,
                                'tax'      => 'vat0',
                            ],
                        ],
                        'payments' => [
                            [
                                'type' => 1,
                                'sum'  => '1 111.01',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testAcceptCheck(): void
    {
        $_data = [
            'external_id' => 1,
            'receipt'     => [
                'total'    => 10.02,
                'items'    => [
                    [
                        'name'     => 'test',
                        'price'    => 10,
                        'quantity' => 2,
                        'tax'      => 'none',
                    ],
                ],
                'payments' => [
                    [
                        'type' => 1,
                        'sum'  => '1111.01',
                    ],
                ],
            ],
            'service'     => [
                'callback_url' => 'test',
            ],
        ];

        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();


        $action = new Normal($entityManager);

        $response = $action->accept($_data, Processing::OPERATION_SELL, new Shop());
        $this->assertIsArray($response);
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('accept', $response['status']);
    }

    public function testEmptyCreatePayments(): void
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $method = new \ReflectionMethod(
            Normal::class, 'createPayments'
        );

        $method->setAccessible(true);

        $doc = new DocIn();
        $method->invoke(new Normal($entityManager), $doc, []);
        $isset1081 = false;
        $isset1215 = false;
        $isset1216 = false;
        foreach ($doc->toArray()['document']['data']['fiscprops'] as $fiscprop) {
            if ($fiscprop['tag'] === 1081) {
                $isset1081 = true;
                continue;
            }
            if ($fiscprop['tag'] === 1215) {
                $isset1215 = true;
                continue;
            }
            if ($fiscprop['tag'] === 1216) {
                $isset1216 = true;
                continue;
            }
        }

        $this->assertFalse($isset1081);
        $this->assertFalse($isset1215);
        $this->assertFalse($isset1216);
    }

    public function testCreateOnePayments(): void
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $method = new \ReflectionMethod(
            Normal::class, 'createPayments'
        );

        $method->setAccessible(true);

        $doc = new DocIn();
        $payments = [
            1081 => [
                'type' => 1,
                'sum'  => 1,
            ],
        ];

        $method->invoke(new Normal($entityManager), $doc, $payments);
        $isset1081 = false;
        $isset1215 = false;
        $isset1216 = false;
        foreach ($doc->toArray()['document']['data']['fiscprops'] as $fiscprop) {
            if ($fiscprop['tag'] === 1081) {
                $isset1081 = true;
                continue;
            }
            if ($fiscprop['tag'] === 1215) {
                $isset1215 = true;
                continue;
            }
            if ($fiscprop['tag'] === 1216) {
                $isset1216 = true;
                continue;
            }
        }

        $this->assertFalse($isset1081);
        $this->assertFalse($isset1215);
        $this->assertFalse($isset1216);
    }

    public function testCreatePayments(): void
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $method = new \ReflectionMethod(
            Normal::class, 'createPayments'
        );

        $method->setAccessible(true);

        $doc = new DocIn();
        $payments = [
            1081 => [
                'type' => 1,
                'sum'  => 1,
            ],
            1215 => [
                'type' => 2,
                'sum'  => 2,
            ],
            1216 => [
                'type' => 3,
                'sum'  => 3,
            ],
        ];

        $method->invoke(new Normal($entityManager), $doc, $payments);

        foreach ($doc->toArray()['document']['data']['fiscprops'] as $fiscprop) {
            if (isset($payments[$fiscprop['tag']])) {
                $this->assertEquals($payments[$fiscprop['tag']]['sum'] * 100, $fiscprop['value']);
            }
        }
    }

    /**
     * @param array $request
     * @param array $response
     *
     * @dataProvider itemProvider
     * @throws \ReflectionException
     */
    public function testAddCheckItem(array $request, array $response): void
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $method = new \ReflectionMethod(Normal::class, 'addCheckItem');
        $method->setAccessible(true);

        /** @var SubjectCalculation $res */
        $res = $method->invoke(new Normal($entityManager), $request);
        $this->assertInstanceOf(SubjectCalculation::class, $res);

        $this->assertEquals($response, $res->toArray());
    }

    public function itemProvider(): array
    {
        return [
            [
                [
                    'type'     => 1,
                    'mode'     => 1,
                    'name'     => 'test',
                    'price'    => 10,
                    'quantity' => 2,
                    'tax'      => 'none',
                ],
                [
                    'tag'       => 1059,
                    'fiscprops' => [
                        [
                            'tag'   => 1214,
                            'value' => 1,
                        ],
                        [
                            'tag'   => 1212,
                            'value' => 1,
                        ],
                        [
                            'tag'   => 1030,
                            'value' => 'test',
                        ],
                        [
                            'tag'   => 1079,
                            'value' => 1000,
                        ],
                        [
                            'tag'   => 1023,
                            'value' => "2.000",
                        ],
                        [
                            'tag'   => 1199,
                            'value' => 6,
                        ],
                    ],
                ],
            ],
            [
                [
                    'type'     => 1,
                    'mode'     => 1,
                    'name'     => 'test',
                    'price'    => 10,
                    'quantity' => 2,
                    'tax'      => 'vat18',
                ],
                [
                    'tag'       => 1059,
                    'fiscprops' => [
                        [
                            'tag'   => 1214,
                            'value' => 1,
                        ],
                        [
                            'tag'   => 1212,
                            'value' => 1,
                        ],
                        [
                            'tag'   => 1030,
                            'value' => 'test',
                        ],
                        [
                            'tag'   => 1079,
                            'value' => 1000,
                        ],
                        [
                            'tag'   => 1023,
                            'value' => "2.000",
                        ],
                        [
                            'tag'   => 1199,
                            'value' => 1,
                        ],
                    ],
                ],
            ],
            [
                [
                    'type'     => 1,
                    'mode'     => 1,
                    'name'     => 'test',
                    'price'    => 10,
                    'quantity' => 2,
                    'tax'      => 'vat20',
                ],
                [
                    'tag'       => 1059,
                    'fiscprops' => [
                        [
                            'tag'   => 1214,
                            'value' => 1,
                        ],
                        [
                            'tag'   => 1212,
                            'value' => 1,
                        ],
                        [
                            'tag'   => 1030,
                            'value' => 'test',
                        ],
                        [
                            'tag'   => 1079,
                            'value' => 1000,
                        ],
                        [
                            'tag'   => 1023,
                            'value' => "2.000",
                        ],
                        [
                            'tag'   => 1199,
                            'value' => 1,
                        ],
                    ],
                ],
            ],
            [
                [
                    'type'     => 1,
                    'mode'     => 1,
                    'name'     => 'test',
                    'price'    => 10,
                    'quantity' => 2,
                    'tax'      => 'vat10',
                ],
                [
                    'tag'       => 1059,
                    'fiscprops' => [
                        [
                            'tag'   => 1214,
                            'value' => 1,
                        ],
                        [
                            'tag'   => 1212,
                            'value' => 1,
                        ],
                        [
                            'tag'   => 1030,
                            'value' => 'test',
                        ],
                        [
                            'tag'   => 1079,
                            'value' => 1000,
                        ],
                        [
                            'tag'   => 1023,
                            'value' => "2.000",
                        ],
                        [
                            'tag'   => 1199,
                            'value' => 2,
                        ],
                    ],
                ],
            ],
            [
                [
                    'type'     => 1,
                    'mode'     => 1,
                    'name'     => 'test',
                    'price'    => 10,
                    'quantity' => 2,
                    'tax'      => 'vat118',
                ],
                [
                    'tag'       => 1059,
                    'fiscprops' => [
                        [
                            'tag'   => 1214,
                            'value' => 1,
                        ],
                        [
                            'tag'   => 1212,
                            'value' => 1,
                        ],
                        [
                            'tag'   => 1030,
                            'value' => 'test',
                        ],
                        [
                            'tag'   => 1079,
                            'value' => 1000,
                        ],
                        [
                            'tag'   => 1023,
                            'value' => "2.000",
                        ],
                        [
                            'tag'   => 1199,
                            'value' => 3,
                        ],
                    ],
                ],
            ],
            [
                [
                    'type'     => 1,
                    'mode'     => 1,
                    'name'     => 'test',
                    'price'    => 10,
                    'quantity' => 2,
                    'tax'      => 'vat120',
                ],
                [
                    'tag'       => 1059,
                    'fiscprops' => [
                        [
                            'tag'   => 1214,
                            'value' => 1,
                        ],
                        [
                            'tag'   => 1212,
                            'value' => 1,
                        ],
                        [
                            'tag'   => 1030,
                            'value' => 'test',
                        ],
                        [
                            'tag'   => 1079,
                            'value' => 1000,
                        ],
                        [
                            'tag'   => 1023,
                            'value' => "2.000",
                        ],
                        [
                            'tag'   => 1199,
                            'value' => 3,
                        ],
                    ],
                ],
            ],
            [
                [
                    'type'     => 1,
                    'mode'     => 1,
                    'name'     => 'test',
                    'price'    => 10,
                    'quantity' => 2,
                    'tax'      => 'vat110',
                ],
                [
                    'tag'       => 1059,
                    'fiscprops' => [
                        [
                            'tag'   => 1214,
                            'value' => 1,
                        ],
                        [
                            'tag'   => 1212,
                            'value' => 1,
                        ],
                        [
                            'tag'   => 1030,
                            'value' => 'test',
                        ],
                        [
                            'tag'   => 1079,
                            'value' => 1000,
                        ],
                        [
                            'tag'   => 1023,
                            'value' => "2.000",
                        ],
                        [
                            'tag'   => 1199,
                            'value' => 4,
                        ],
                    ],
                ],
            ],
            [
                [
                    'type'     => 1,
                    'mode'     => 1,
                    'name'     => 'test',
                    'price'    => 10,
                    'quantity' => 2,
                    'tax'      => 'vat0',
                ],
                [
                    'tag'       => 1059,
                    'fiscprops' => [
                        [
                            'tag'   => 1214,
                            'value' => 1,
                        ],
                        [
                            'tag'   => 1212,
                            'value' => 1,
                        ],
                        [
                            'tag'   => 1030,
                            'value' => 'test',
                        ],
                        [
                            'tag'   => 1079,
                            'value' => 1000,
                        ],
                        [
                            'tag'   => 1023,
                            'value' => "2.000",
                        ],
                        [
                            'tag'   => 1199,
                            'value' => 5,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $request
     * @param array $response
     *
     * @dataProvider prepareSendProvider
     */
    public function testPrepareSend(array $request, array $response): void
    {
        /** @var Normal $class */
        $class = $this->getMockBuilder(Normal::class)
            ->setMethods()
            ->disableOriginalConstructor()
            ->getMock();

        $processing = new Processing();
        $processing->setShop((new Shop())->setCompany((new Company())->setInn(123456)));
        $processing->setRawData(Json::encode($request))
            ->setOperation(Processing::OPERATION_SELL);

        $kkt = new Kkt();


        $res = $class->prepareSend($processing, $kkt);
        $this->assertInstanceOf(DocIn::class, $res);
        $this->assertNotNull($processing->getSessionId());
        $this->assertEquals($res->getSessionId(), $processing->getSessionId());

        $res->setSessionId('session');
        $this->assertEquals($response, $res->toArray());
    }

    public function prepareSendProvider(): array
    {
        return [
            [
                [
                    'receipt' => [
                        'attributes' => [
                            'email' => 'test@test.ru',
                        ],
                        'total'      => 100,
                        'items'      => [
                            [
                                'type'     => 1,
                                'quantity' => 10,
                                'name'     => 'test',
                                'price'    => 10,
                                'tax'      => 'vat20',
                            ],
                        ],
                        'payments'   => [
                            'type' => 1,
                            'sum'  => 100,
                        ],
                    ],
                ],
                [
                    'document' => [
                        'print'     => 0,
                        'sessionId' => 'session',
                        'data'      => [
                            'docName'   => 'Кассовый чек',
                            'moneyType' => 2,
                            'sum'       => 10000,
                            'fiscprops' => [
                                [
                                    'tag'   => 1055,
                                    'value' => 1,
                                ],
                                [
                                    'tag'   => 1018,
                                    'value' => 123456,
                                ],
                                [
                                    'tag'   => 1054,
                                    'value' => 1,
                                ],
                                [
                                    'tag' => 1037,
                                ],
                                [
                                    'tag'   => 1008,
                                    'value' => 'test@test.ru',
                                ],
                                [
                                    'tag'       => 1059,
                                    'fiscprops' => [

                                        [
                                            'tag'   => 1214,
                                            'value' => 4,
                                        ],

                                        [
                                            'tag'   => 1212,
                                            'value' => 1,
                                        ],

                                        [
                                            'tag'   => 1030,
                                            'value' => 'test',
                                        ],

                                        [
                                            'tag'   => 1079,
                                            'value' => 1000,
                                        ],

                                        [
                                            'tag'   => 1023,
                                            'value' => 10.000,
                                        ],

                                        [
                                            'tag'   => 1199,
                                            'value' => 1,
                                        ],

                                    ],

                                ],

                            ],
                            'type'      => 1,
                        ],

                    ],

                ],
            ],
            'string to flost total' => [
                [
                    'receipt' => [
                        'attributes' => [
                            'email' => 'test@test.ru',
                        ],
                        'total'      => '1 000.01',
                        'items'      => [
                            [
                                'type'     => 1,
                                'quantity' => 10,
                                'name'     => 'test',
                                'price'    => 10,
                                'tax'      => 'vat20',
                            ],
                        ],
                        'payments'   => [
                            'type' => 1,
                            'sum'  => 100,
                        ],
                    ],
                ],
                [
                    'document' => [
                        'print'     => 0,
                        'sessionId' => 'session',
                        'data'      => [
                            'docName'   => 'Кассовый чек',
                            'moneyType' => 2,
                            'sum'       => 100001,
                            'fiscprops' => [
                                [
                                    'tag'   => 1055,
                                    'value' => 1,
                                ],
                                [
                                    'tag'   => 1018,
                                    'value' => 123456,
                                ],
                                [
                                    'tag'   => 1054,
                                    'value' => 1,
                                ],
                                [
                                    'tag' => 1037,
                                ],
                                [
                                    'tag'   => 1008,
                                    'value' => 'test@test.ru',
                                ],
                                [
                                    'tag'       => 1059,
                                    'fiscprops' => [

                                        [
                                            'tag'   => 1214,
                                            'value' => 4,
                                        ],

                                        [
                                            'tag'   => 1212,
                                            'value' => 1,
                                        ],

                                        [
                                            'tag'   => 1030,
                                            'value' => 'test',
                                        ],

                                        [
                                            'tag'   => 1079,
                                            'value' => 1000,
                                        ],

                                        [
                                            'tag'   => 1023,
                                            'value' => 10.000,
                                        ],

                                        [
                                            'tag'   => 1199,
                                            'value' => 1,
                                        ],

                                    ],

                                ],

                            ],
                            'type'      => 1,
                        ],

                    ],

                ],
            ],
            [
                [
                    'receipt' => [
                        'attributes' => [
                            'phone' => '+799999999',
                        ],
                        'total'      => 100,
                        'items'      => [
                            [
                                'type'     => 1,
                                'quantity' => 10,
                                'name'     => 'test',
                                'price'    => 10,
                                'tax'      => 'vat20',
                            ],
                        ],
                        'payments'   => [
                            'type' => 1,
                            'sum'  => 100,
                        ],
                    ],
                ],
                [
                    'document' => [
                        'print'     => 0,
                        'sessionId' => 'session',
                        'data'      => [
                            'docName'   => 'Кассовый чек',
                            'moneyType' => 2,
                            'sum'       => 10000,
                            'fiscprops' => [
                                [
                                    'tag'   => 1055,
                                    'value' => 1,
                                ],

                                [
                                    'tag'   => 1018,
                                    'value' => 123456,
                                ],

                                [
                                    'tag'   => 1054,
                                    'value' => 1,
                                ],


                                [
                                    'tag' => 1037,
                                ],


                                [
                                    'tag'   => 1008,
                                    'value' => '+799999999',
                                ],


                                [
                                    'tag'       => 1059,
                                    'fiscprops' => [

                                        [
                                            'tag'   => 1214,
                                            'value' => 4,
                                        ],

                                        [
                                            'tag'   => 1212,
                                            'value' => 1,
                                        ],

                                        [
                                            'tag'   => 1030,
                                            'value' => 'test',
                                        ],

                                        [
                                            'tag'   => 1079,
                                            'value' => 1000,
                                        ],

                                        [
                                            'tag'   => 1023,
                                            'value' => 10.000,
                                        ],

                                        [
                                            'tag'   => 1199,
                                            'value' => 1,
                                        ],

                                    ],

                                ],

                            ],
                            'type'      => 1,

                        ],

                    ],

                ],
            ],
            'Buyer inn and name'    => [
                [
                    'receipt' => [
                        'attributes' => [
                            'phone' => '+799999999',
                            'name'  => 'test user',
                            'inn'   => '123456789',
                        ],
                        'total'      => 100,
                        'items'      => [
//                            [
//                                'type'     => 1,
//                                'quantity' => 10,
//                                'name'     => 'test',
//                                'price'    => 10,
//                                'tax'      => 'vat20',
//                            ],
                        ],
                        'payments'   => [
                            'type' => 1,
                            'sum'  => 100,
                        ],
                    ],
                ],
                [
                    'document' => [
                        'print'     => 0,
                        'sessionId' => 'session',
                        'data'      => [
                            'docName'   => 'Кассовый чек',
                            'moneyType' => 2,
                            'sum'       => 10000.0,
                            'fiscprops' => [
                                [
                                    'tag'   => 1055,
                                    'value' => 1,
                                ],
                                [
                                    'tag'   => 1018,
                                    'value' => 123456,
                                ],
                                [
                                    'tag'   => 1054,
                                    'value' => 1,
                                ],
                                [
                                    'tag'   => 1227,
                                    'value' => 'test user',
                                ],
                                [
                                    'tag'   => 1228,
                                    'value' => '123456789',
                                ],
                                [
                                    'tag' => 1037,
                                ],
                                [
                                    'tag'   => 1008,
                                    'value' => '+799999999',
                                ],
                            ],
                            'type'      => 1,
                        ],
                    ],
                ],
            ],
        ];
    }
}
