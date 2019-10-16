<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 03.04.18
 * Time: 20:03
 */

namespace Office\Service;

use App\Entity\Setting;
use Cms\Service\SettingService;
use Doctrine\ORM\EntityManager;
use Office\Entity\Kkt;
use Zend\Json\Json;

/**
 * Class Umka
 *
 * @package Office\Service
 */
class Umka
{
    public const REASON_IDS = [
        -1 => 'Первичная регистраци',
        1  => 'Замена фискального накопителя',
        2  => 'Замена ОФД',
        3  => 'Изменение реквизитов',
        4  => 'Изменение настроек ККТ',
    ];
    protected const LOGIN_URL = 'LoginSession';
    protected const DATA_URL = 'WebDataPackPost';

    private $entityManager;

    /** @var array */
    private $session;

    /** @var Setting[] */
    private $settings;

    /**
     * Umka constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->settings = $this->entityManager->getRepository(Setting::class)->createQueryBuilder('s', 's.param')->getQuery()->getResult();
    }

    /**
     * Авторизация
     *
     * @return array
     * @throws \Exception
     */
    public function login(): array
    {
        if ($this->session === null) {
            $data = [
                'login'    => $this->settings[SettingService::UMKA_LOGIN]->getValue(),
                'password' => $this->settings[SettingService::UMKA_PASSWORD]->getValue(),
            ];

            $response = $this->send(self::LOGIN_URL, $data);

            if (!isset($response['idSession']) || $response['idSession'] === null) {
                throw new \Exception('Error login');
            }

            $this->session = $response;
        }

        return $this->session;
    }

    /**
     * Получение доступного кол-ва ккт
     *
     * @return int
     * @throws \Exception
     */
    public function getAvailableKkt(): int
    {
        $data = [
            'params' => [
                'ext' => [
                    'getAvailableKkms' => true,
                ],
            ],
        ];

        $response = $this->send(self::DATA_URL, $data);

        return $response['ext']['availableKkms'];
    }

    /**
     * Аренда кассы
     *
     * @return bool
     * @throws \Exception
     */
    public function rentKkt(): bool
    {
        $data = [
            'params' => [
                'ext' => [
                    'rentKkms' => [
                        'commandId' => substr(uniqid(time(), true), 0, 32),
                        'commandDt' => date('c'),
                        'action'    => 2,
                        'cnt'       => 1,
                    ],
                ],
            ],
        ];

        $response = $this->send(self::DATA_URL, $data);

        return isset($response['ext']['rentKkms']['result']) && $response['ext']['rentKkms']['result'] === 'OK';
    }

    /**
     * Получение списка всех не зарегистрированных ККТ
     *
     * @return array
     * @throws \Exception
     */
    public function getAllNotRegistredKkt(): array
    {
        $data = [
            'params' => [
                'ext' => [
//                    'getKkmByInn' => ['*'],
                    'getKkmWithoutInn' => true,
                ],
            ],
        ];

        $response = $this->send(self::DATA_URL, $data);

        return $response['ext']['kkms'];
    }

    /**
     * Получение всех касс
     *
     * @return array
     * @throws \Exception
     */
    public function getAllKkt(): array
    {
        $data = [
            'params' => [
                'ext' => [
                    'getKkmByInn'      => ['*'],
                    'getKkmWithoutInn' => true,
                ],
            ],
        ];

        $response = $this->send(self::DATA_URL, $data);

        return $response['ext']['kkms'];
    }

    /**
     * Получение информации по кассе
     *
     * @param Kkt $kkt
     *
     * @return array|null
     * @throws \Exception
     */
    public function getKktInfo(Kkt $kkt): ?array
    {
        $kkms = $this->getAllKkt();
        foreach ($kkms as $kkm) {
            if ($kkm['serialNo'] === $kkt->getSerialNumber()) {
                return $kkm;
            }
        }

        return null;
    }

    /**
     * Удаление ККТ
     *
     * @param string $serialNumber
     *
     * @return bool
     * @throws \Exception
     */
    public function removeKkt(string $serialNumber): bool
    {
        $data = [
            'params' => [
                'ext' => [
                    'rentKkms' => [
                        'commandId'     => substr(uniqid(time(), true), 0, 32),
                        'commandDt'     => date('c'),
                        'action'        => -2,
                        'serialNumbers' => [$serialNumber],
                    ],
                ],
            ],
        ];

        $response = $this->send(self::DATA_URL, $data);

        return isset($response['ext']['rentKkms']['result']) && $response['ext']['rentKkms']['result'] === 'OK';
    }

    /**
     * @param Kkt $kkt
     * @param int $idReason
     * @param bool $_isTest
     *
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function fiscalizeKkt(Kkt $kkt, int $idReason = -1, bool $_isTest = false): bool
    {
        $settings = $this->settings;

        $shop = $kkt->getShop();
        $company = $shop->getCompany();
        $ofd = $company->getOfd();

        $kkt->setPaymentAddress($settings[SettingService::KKT_LOCATION]->getValue());

        $nalogSystem = (int)$company->getNalogSystem();
//        $nalogSystem = -1; //Чтобы касса принимала любые системы налогообложения
        //Соколов зарубил это на корню, указываем жестко
        $paymentPlacement = !empty($shop->getUrl()) ? $shop->getUrl() : $shop->getAddress();
        $data = [
            'ownerName'        => $company->getDefaultTitle(),
            //1048 - наименование пользователя хозяина
            'ownerInn'         => $company->getInn(),
            //1018 - ИНН пользователя хозяина
            'ownerEmail'       => $company->getCompanyCheckEmail(),
            //1117 - адрес электронной почты отправителя чека хозяина
            'paymentAddress'   => $kkt->getPaymentAddress(),
            //1009 - адрес расчетов
            'paymentPlacement' => $paymentPlacement,
            //1187 - место расчетов ( в углу под лестницей)
            'cashierName'      => 'Bitrix',
            //1021 - кассир
            'cashierInn'       => $company->getInn(),
            //1203 - ИНН кассира

            'agentIsBankPayAgent'    => false,
            //1057 - признак агента (табл.10) бит 0
            'agentIsBankPaySubagent' => false,
            //1057 - признак агента (табл.10) бит 1
            'agentIsPayAgent'        => false,
            //1057 - признак агента (табл.10) бит 2
            'agentIsPaySubagent'     => false,
            //1057 - признак агента (табл.10) бит 3
            'agentIsPlenipotentiary' => false,
            //1057 - признак агента (табл.10) бит 4
            'agentIsCommissioner'    => false,
            //1057 - признак агента (табл.10) бит 5
            'agentIsAgent'           => false,
            //1057 - признак агента (табл.10) бит 6

            'taxSystemOCH'    => $nalogSystem === 0,
            //1062 - системы налогообложения (табл.9) бит 0
            'taxSystemECHg'   => $nalogSystem === 1,
            //1062 - системы налогообложения (табл.9) бит 1
            'taxSystemECHgp'  => $nalogSystem === 2,
            //1062 - системы налогообложения (табл.9) бит 2
            'taxSystemEHBg'   => $nalogSystem === 3,
            //1062 - системы налогообложения (табл.9) бит 3
            'taxSystemECXH'   => $nalogSystem === 4,
            //1062 - системы налогообложения (табл.9) бит 4
            'taxSystemPatent' => $nalogSystem === 5,
            //1062 - системы налогообложения (табл.9) бит 5

            'regNo'              => $kkt->getRegNumber(),
            //1037 - регистрационный номер ККТ
            'serialNo'           => $kkt->getSerialNumber(),
            //1013 - заводской номер ККТ
            'terminalMode'       => false,
            //1001 - признак автоматического режима
            'terminalHasPrinter' => false,
            //1221 - признак установки принтера в автомате (only if kktIsAutomatic)

            'terminalSerialNo'    => '',
            //1036 - номер автомата (only if kktIsAutomatic)
            'kktUsingForExcise'   => false,
            //1207 - признак торговли подакцизными товарами
            'kktUsingForService'  => false,
            //1109 - признак расчетов за услуги
            'kktUsingForGambling' => false,
            //1193 - признак проведения азартных игр
            'kktUsingForLottery'  => false,
            //1126 - признак проведения лотереи
            'kktUsingForWebOnly'  => true,
            //1108 - признак ККТ для расчетов только в Интернет
            'kktUsingForBsoOnly'  => false,
            //1110 - признак АС БСО

            'isAutonomous' => false,
            //1002 - признак автономного режима
            'isCrypted'    => true,
            //1056 - признак шифрования

            'fnsUrl'  => $ofd->getUrlNalog(),
            //1060 - адрес сайта ФНС
            'ofdName' => $ofd->getTitle(),
            //1046 - наименование ОФД
            'ofdInn'  => $ofd->getInn(),
            //1017 - ИНН ОФД

            'idReason' => $idReason
            // причина перерегистрации
            //-1 (минус 1) - Первичная регистраци
            //1-  Замена фискального накопителя
            //2 - Замена ОФД
            //3 - Изменение реквизитов
            //4 - Изменение настроек ККТ
        ];
        if ($_isTest) {
            $data['idOfd'] = 14;
        }
        $commandName = substr(uniqid(time(), true), 0, 32);
        $sendData = [
            'params' => [
                'ext' => [
                    'fiscCommands' => [
                        [
                            'commandId'              => $commandName,
                            'commandDt'              => date('c'),
                            'fiscalRegistrationInfo' => $data,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->send(self::DATA_URL, $sendData);

        if (!isset($response['ext']['fiscCommands'][0])) {
            return false;
        }

        $kkt->setFiscalCommand($commandName);
        $this->entityManager->persist($kkt);
        $this->entityManager->flush();

        $res = $response['ext']['fiscCommands'][0];

        return $res['result'] === 'OK';

//        foreach ($res as $item) {
//
//        }

//        return false;
    }

    /**
     * @param Kkt $kkt
     *
     * @return array
     * @throws \Exception
     */
    public function getFiscalResult(Kkt $kkt): array
    {
        $data = [
            'params' => [
                'ext' => [
                    'getFiscCommandtResults' => [$kkt->getSerialNumber()],
                ],
            ],
        ];

        return $this->send(self::DATA_URL, $data);
    }

    /**
     * @param Kkt $kkt
     * @param null $dateFrom
     * @param null $dateTo
     *
     * @return array
     * @throws \Exception
     */
    public function getDocuments(Kkt $kkt, $dateFrom = null, $dateTo = null): array
    {
        $data = [
            'params' => [
                'ext' => [
                    'getDocumentsCommands' => [
                        'commandId'     => substr(uniqid(time(), true), 0, 32),
                        'commandDt'     => date('c'),
                        'dateFrom'      => date('c', $dateFrom ?? (time() - 60 * 60 * 24)),
                        'dateTo'        => date('c', $dateTo ?? time()),
                        'serialNumbers' => [$kkt->getSerialNumber()],
                    ],
                ],
            ],
        ];

        return $this->send(self::DATA_URL, $data);
    }

    /**
     * Отправка запросов
     *
     * @param string $type
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function send(string $type, array $data, array $getParams = null): array
    {
        $host = $this->settings[SettingService::UMKA_HOST]->getValue();
        $curl = curl_init();

        $url = $host.'/kkm-trade/api/'.$type.($type === self::LOGIN_URL ? '?'.http_build_query($data) : '');
        if ($getParams !== null) {
            $url .= '?'.http_build_query($getParams);
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if ($type !== self::LOGIN_URL) {
            $dataSession = [];
            if ($type === self::DATA_URL) {
                $this->login();
                $dataSession = [
                    'session' => [
                        'idSession' => $this->session['idSession'],
                        'idDealer'  => $this->session['idDealer'],
                        'idUser'    => $this->session['idUser'],
                    ],
                ];
            }

            $jsonString = Json::encode(array_merge($data, $dataSession));
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonString);
            curl_setopt(
                $curl,
                CURLOPT_HTTPHEADER,
                [
                    'Content-Type: application/json',
                    'Content-Length: '.strlen($jsonString),
                ]
            );
        }

        $out = curl_exec($curl);
        curl_close($curl);

        return Json::decode($out, Json::TYPE_ARRAY);
    }
}
