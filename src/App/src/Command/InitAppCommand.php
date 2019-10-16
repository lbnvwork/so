<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 09.02.18
 * Time: 14:26
 */

namespace App\Command;

use App\Entity\Service;
use App\Entity\Setting;
use App\Service\DateTime;
use Auth\Entity\User;
use Auth\Entity\UserHasRole;
use Cms\Service\SettingService;
use Doctrine\ORM\EntityManager;
use Office\Entity\Company;
use Office\Entity\Kkt;
use Office\Entity\Ofd;
use Office\Entity\Shop;
use Office\Entity\Tariff;
use Permission\Entity\Role;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog\Logger;

/**
 * Class InitAppCommand
 *
 * @package App\Command
 */
class InitAppCommand extends Command
{
    private $logger;

    private $entityManager;

    /**
     * InitAppCommand constructor.
     *
     * @param Logger $logger
     * @param EntityManager $entityManager
     */
    public function __construct(Logger $logger, EntityManager $entityManager)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    /**
     * Configures the command
     */
    protected function configure(): void
    {
        $this
            ->setName('app:init')
            ->setDescription('Инициализация приложения');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->info('Инициализация приложения');
        $ofd = new Ofd();
        $ofd->setTitle('Первый ОФД')
            ->setServer('k-server.1-ofd.ru')
            ->setPort(7777)
            ->setTimeout(10000)
            ->setInn('7709364346')
            ->setUrl('1-ofd.ru')
            ->setUrlNalog('nalog.ru')
            ->setTimerConnect(30)
            ->setTimerRequest(30)
            ->setIsEnabled(true);
        $this->entityManager->persist($ofd);
        $this->entityManager->flush();

        $settings = [
            [
                'id'         => '1',
                'param'      => 'emailForCompany',
                'value'      => 'admin@schetmash.test',
                'group_name' => 'main',
            ],
            [
                'id'         => '2',
                'param'      => 'emailForShop',
                'value'      => 'admin@schetmash.test',
                'group_name' => 'main',
            ],
            [
                'id'         => '3',
                'param'      => 'emailForKkt',
                'value'      => 'admin@schetmash.test',
                'group_name' => 'main',
            ],
            [
                'id'         => '4',
                'param'      => 'minMonth',
                'value'      => '3',
                'group_name' => 'main',
            ],
            [
                'id'         => '5',
                'param'      => 'kktPrice',
                'value'      => '1000',
                'group_name' => 'main',
            ],
            [
                'id'         => '6',
                'param'      => 'fnPrice',
                'value'      => '1000',
                'group_name' => 'main',
            ],
            [
                'id'         => '7',
                'param'      => 'kktService',
                'value'      => 'Услуга ',
                'group_name' => 'main',
            ],
            [
                'id'         => '8',
                'param'      => 'fnService',
                'value'      => 'Накопитель фискальный ФН-1.1',
                'group_name' => 'main',
            ],
            [
                'id'         => '15',
                'param'      => SettingService::UMKA_HOST,
                'value'      => 'armaks_server:2345',
                'group_name' => 'main',
            ],
            [
                'id'         => '9',
                'param'      => 'umkaLogin',
                'value'      => 'test',
                'group_name' => 'main',
            ],
            [
                'id'         => '10',
                'param'      => 'umkaPassword',
                'value'      => 'test',
                'group_name' => 'main',
            ],
            [
                'id'         => '11',
                'param'      => 'kktLocation',
                'value'      => '305022, ОБЛАСТЬ КУРСКАЯ, ГОРОД КУРСК, УЛИЦА РАБОЧАЯ 2-Я, ДОМ 23, ЛИТ. В 1, ПОМ. 59',
                'group_name' => 'main',
            ],
            [
                'id'         => '12',
                'param'      => 'kktHost',
                'value'      => 'armaks_server:2345',
                'group_name' => 'main',
            ],
            [
                'id'         => '13',
                'param'      => 'cashierLogin',
                'value'      => 'test',
                'group_name' => 'main',
            ],
            [
                'id'         => '14',
                'param'      => 'cashierPassword',
                'value'      => 'test',
                'group_name' => 'main',
            ],
        ];

        foreach ($settings as $setting) {
            $entity = new Setting();
            $entity->setValue($setting['value'])
                ->setGroup($setting['group_name'])
                ->setParam($setting['param']);
            $this->entityManager->persist($entity);
        }

        $services = [
            [
                'id'            => '1',
                'name'          => 'Накопитель фискальный ФН-1.1',
                'price'         => '6000',
                'measure'       => 'шт',
                'default_value' => '1',
            ],
            [
                'id'            => '2',
                'name'          => 'Услуга "web-Касса"',
                'price'         => '1800',
                'measure'       => 'мес.',
                'default_value' => '3',
            ],
            [
                'id'            => '3',
                'name'          => 'Услуги ОФД',
                'price'         => '500',
                'measure'       => 'Год.',
                'default_value' => '1',
            ],
        ];
        foreach ($services as $service) {
            $entity = new Service();
            $entity->setName($service['name'])
                ->setPrice($service['price'])
                ->setMeasure($service['measure'])
                ->setDefaultValue($service['default_value']);
            $this->entityManager->persist($entity);
        }

        $roles = [
            [
                'id'        => '1',
                'parent_id' => null,
                'role_name' => 'office_admin',
                'title'     => 'Пользователь сайта',
            ],
            [
                'id'        => '2',
                'parent_id' => null,
                'role_name' => 'admin',
                'title'     => 'Админ',
            ],
            [
                'id'        => '3',
                'parent_id' => null,
                'role_name' => 'manager',
                'title'     => 'Менеджер',
            ],
            [
                'id'        => '4',
                'parent_id' => null,
                'role_name' => 'main_manager',
                'title'     => 'Главный Менеджер',
            ],
            [
                'id'        => '5',
                'parent_id' => null,
                'role_name' => 'referral',
                'title'     => 'Участник реферальной программы',
            ],
            [
                'id'        => '6',
                'parent_id' => null,
                'role_name' => 'test_api',
                'title'     => 'Тестовый пользователь',
            ],
        ];
        foreach ($roles as $role) {
            $entity = new Role();
            $entity->setRoleName($role['role_name'])
                ->setTitle($role['title']);
            $this->entityManager->persist($entity);
        }


        $data = [
//            'id'             => '1',
//            'referral_id'    => null,
            'email'      => 'admin@schetmash.test',
//            'password'       => '$2y$10$rBy94JS4SZ7MW6KGnyy9ge4sWt.nt.hE1jsZvBA3bQ8E8QinUJnUe',
            'firstName'  => 'admin',
            'middleName' => 'admin',
            'lastName'   => 'admin',
            'phone'      => '02',
            'roboPromo'  => '0',
        ];

        $user = new User();
        foreach ($data as $key => $value) {
            $method = 'set'.ucfirst($key);
            $user->{$method}($value);
        }

        $user->getUserRoleManager()->add((new UserHasRole())->setUser($user)->setRoleName('office_admin'));
        $user->getUserRoleManager()->add((new UserHasRole())->setUser($user)->setRoleName('admin'));
        $user->getUserRoleManager()->add((new UserHasRole())->setUser($user)->setRoleName('main_manager'));

        $user->setNewPassword('admin');
//        $user->setHashKey(str_replace('.', '', uniqid(time(), true)));
        $user->setIsConfirmed(true);
        $user->setIsBeginner(false);

        $this->entityManager->persist($user);

        $data = [
            'id'         => User::TEST_USER_ID,
            'email'      => 'testapi@schetmash.com',
            'firstName'  => 'test',
            'middleName' => 'test',
            'lastName'   => 'test',
            'phone'      => '02',
            'roboPromo'  => '0',
        ];

        $user = new User();
        foreach ($data as $key => $value) {
            $method = 'set'.ucfirst($key);
            $user->{$method}($value);
        }

        $user->getUserRoleManager()->add((new UserHasRole())->setUser($user)->setRoleName('office_admin'));
        $user->setNewPassword('123456789');
        $user->setIsConfirmed(true);
        $user->setIsBeginner(false);

        $this->entityManager->persist($user);

        $tariff = new Tariff();
        $tariff->setIsDefault(true)
            ->setRentCost(100)
            ->setIsPopular(true)
            ->setIsBeginner(false)
            ->setIsPromotime(false)
            ->setTitle('Тестовый тариф');
        $this->entityManager->persist($tariff);

        $company = new Company();
        $company->setTitle('СЧЕТМАШ')
            ->setInn('4632126284')
            ->setKpp('463201001')
            ->setOgrn('1104632009976')
            ->setType('Акционерное общество')
            ->setOrgType(0)
            ->setNalogSystem(0)
            ->setAddress('305022, ОБЛАСТЬ КУРСКАЯ, ГОРОД КУРСК, УЛИЦА РАБОЧАЯ 2-Я, ДОМ 23, ЛИТ. В 1, ПОМ. 59')
            ->setCompanyPhone('+799999999')
            ->setCompanyEmail('testapi@schetmash.com')
            ->setDirectorLastName('Шелехова')
            ->setDirectorMiddleName('Андреевна')
            ->setDirectorFirstName('Валентина')
            ->setCompanyCheckEmail('testapi@schetmash.com')
            ->setIsEnabled(0)
            ->setIsDeleted(0)
            ->setBalance(10000)
            ->setOfd($ofd)
            ->setDate(new \DateTime());
        $this->entityManager->persist($company);
        $this->entityManager->flush();

        $company->addUser($user);
        $user->addCompany($company);

        $shop = new Shop();
        $shop->setTitle('test')
            ->setCompany($company)
            ->setKktParams('[]')
            ->setUrl('https://online.schetmash.com');
        $this->entityManager->persist($shop);

        $bodyFiscal = '"{';
        $bodyFiscal .= '\"t1012\":\"2018-08-27T09:21:00+03:00\",';
        $bodyFiscal .= '\"t1018\":\"4632126284\",';
        $bodyFiscal .= '\"t1021\":\"Bitrix\",';
        $bodyFiscal .= '\"t1037\":\"0024546546059435\",';
        $bodyFiscal .= '\"t1038\":6,';
        $bodyFiscal .= '\"t1040\":30,';
        $bodyFiscal .= '\"t1041\":\"9999078900012080\",';
        $bodyFiscal .= '\"t1050\":false,';
        $bodyFiscal .= '\"t1051\":false,';
        $bodyFiscal .= '\"t1052\":false,';
        $bodyFiscal .= '\"t1053\":true,';
        $bodyFiscal .= '\"t1077\":\"240416a16b3b\",';
        $bodyFiscal .= '\"t1097\":29,';
        $bodyFiscal .= '\"t1098\":\"2018-08-10T14:31:00+03:00\",';
        $bodyFiscal .= '\"t1111\":3,';
        $bodyFiscal .= '\"t1118\":1,';
        $bodyFiscal .= '\"t1203\":\"4632126284\",';
        $bodyFiscal .= '\"t1209\":2}"';
        $kkt = new Kkt();
        $kkt->setShop($shop)
            ->setDateExpired((new DateTime())->addMonths(3))
            ->setIsEnabled(1)
            ->setIsFiscalized(1)
            ->setIsDeleted(0)
            ->setIsSendFn(0)
            ->setFsVersion('fn debug v 2.13')
            ->setTariff($tariff);
        $kkt2 = clone  $kkt;
        $kkt->setSerialNumber('17000675')
            ->setRawData(
                '{
  "id": 2110,
  "chId": "71679584736",
  "deleted": 0,
  "idHardware": 5,
  "serialNo": "17000675",
  "blocked": false,
  "inn": "",
  "regNo": "",
  "eklzNo": "",
  "idKkmServer": 68,
  "idRentStatus": 2,
  "autoCloseCicleAt": "",
  "idCabinet": 2,
  "idDealerRented": 56,
  "lastRegDt": "1859-12-31T21:00:00.000+0000",
  "status": {
    "id": 0,
    "serverDt": "2018-04-09T11:58:44.410+0000",
    "cash": 0,
    "cycleNumber": 0,
    "dt": "2018-04-09T11:57:38.000+0000",
    "fiscalized": false,
    "fsStatus": {
      "fsNumber": "8710000101739435",
      "fsVersion": "fn_v_1_0       ",
      "lifeTime": {
        "availableRegistrations": 30
      },
      "phase": 1,
      "transport": {
        "docIsReading": true,
        "firstDocNumber": 0,
        "offlineDocsCount": 0,
        "state": 0
      }
    },
    "introductions": 0,
    "introductionsSum": 0,
    "lastCheckNumber": 0,
    "linked": true,
    "payouts": 0,
    "payoutsSum": 0
  }
}'
            )
            ->setFsNumber('9999078900012080')
            ->setRegNumber('0024546546059435')
            ->setFiscalRawData(
                '{
  "id": 6195588,
  "downloadDt": "2018-08-27T06:20:21.868+0000",
  "docDt": "2018-08-27T06:21:33.197+0000",
  "docKind": 5,
  "docNo": 30,
  "fnNo": 9999078900012080,
  "kkmRegNo": 24546546059435,
  "fiscalCode": 379677499,
  "serialNo": 17000675,
  "body": '.$bodyFiscal.'
}'
            )
            ->setFiscalCommand('15353568955b83afdf6f8616.2344018');
        $this->entityManager->persist($kkt);

        $bodyFiscal = '"{';
        $bodyFiscal .= '\"t1001\":false,';
        $bodyFiscal .= '\"t1002\":false,';
        $bodyFiscal .= '\"t1009\":\"305022, ОБЛАСТЬ КУРСКАЯ, ГОРОД КУРСК, УЛИЦА РАБОЧАЯ 2-Я, ДОМ 23, ЛИТ. В 1, ПОМ. 59\",';
        $bodyFiscal .= '\"t1012\":\"2018-09-20T13:19:00+02:30\",';
        $bodyFiscal .= '\"t1013\":\"11200001\",';
        $bodyFiscal .= '\"t1017\":\"7709364346\",';
        $bodyFiscal .= '\"t1018\":\"4632118082\",';
        $bodyFiscal .= '\"t1021\":\"Bitrix\",';
        $bodyFiscal .= '\"t1037\":\"0000004664017223\",';
        $bodyFiscal .= '\"t1040\":3,';
        $bodyFiscal .= '\"t1041\":\"9999078900012846\",';
        $bodyFiscal .= '\"t1046\":\"Первый ОФД\",';
        $bodyFiscal .= '\"t1048\":\"СЧЕТМАШ\",';
        $bodyFiscal .= '\"t1056\":true,';
        $bodyFiscal .= '\"t1060\":\"nalog.ru\",';
        $bodyFiscal .= '\"t1062\":1,';
        $bodyFiscal .= '\"t1077\":\"22041b3af314\",';
        $bodyFiscal .= '\"t1101\":2,';
        $bodyFiscal .= '\"t1108\":true,';
        $bodyFiscal .= '\"t1109\":false,';
        $bodyFiscal .= '\"t1110\":false,';
        $bodyFiscal .= '\"t1117\":\"testapi@schetmash.com\",';
        $bodyFiscal .= '\"t1126\":false,';
        $bodyFiscal .= '\"t1187\":\"https://online.schetmash.com\",';
        $bodyFiscal .= '\"t1188\":\"0.1\",';
        $bodyFiscal .= '\"t1189\":2,';
        $bodyFiscal .= '\"t1193\":false,';
        $bodyFiscal .= '\"t1203\":\"4632126284\",';
        $bodyFiscal .= '\"t1207\":false,';
        $bodyFiscal .= '\"t1209\":2,';
        $bodyFiscal .= '\"t1221\":false';
        $bodyFiscal .= '}"';
        $kkt2->setSerialNumber('11200001')
            ->setRawData(
                '{
  "id": 3878,
  "chId": "78154704898",
  "deleted": 0,
  "idHardware": 5,
  "serialNo": "17000797",
  "blocked": false,
  "inn": "",
  "regNo": "",
  "eklzNo": "",
  "idKkmServer": 71,
  "idRentStatus": 2,
  "idCabinet": 2,
  "idDealerRented": 56,
  "lastRegDt": "1859-12-31T21:00:00.000+0000",
  "status": {
    "id": 0,
    "serverDt": "2018-09-18T10:38:04.533+0000",
    "cash": 0,
    "cycleNumber": 0,
    "dt": "2018-09-18T13:42:30.000+03:00",
    "fiscalized": false,
    "fsStatus": {
      "fsNumber": "9286000100140375",
      "fsVersion": "fn_v_1_1       ",
      "lifeTime": {
        "availableRegistrations": 30
      },
      "phase": 1,
      "transport": {
        "docIsReading": true,
        "firstDocNumber": 0,
        "offlineDocsCount": 0,
        "state": 0
      }
    },
    "introductions": 0,
    "introductionsSum": 0,
    "lastCheckNumber": 0,
    "linked": true,
    "payouts": 0,
    "payoutsSum": 0
  }
}'
            )
            ->setFsNumber('9999078900012846')
            ->setRegNumber('0000004664033224')
            ->setFiscalRawData(
                '{
  "id": 8531823,
  "downloadDt": "2018-09-20T10:27:17.766+0000",
  "docDt": "2018-09-20T10:49:14.171+0000",
  "docKind": 1,
  "docNo": 3,
  "fnNo": 9999078900012846,
  "kkmRegNo": 4664033224,
  "fiscalCode": 456848148,
  "serialNo": 11200001,
  "body": '.$bodyFiscal.'
}'
            )
            ->setFiscalCommand('15374391035ba3757f62ec15.3637233')
            ->setTariff($tariff);
        $this->entityManager->persist($kkt2);

        $this->entityManager->flush();
    }
}
