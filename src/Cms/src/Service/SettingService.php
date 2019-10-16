<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 26.01.18
 * Time: 11:55
 */

namespace Cms\Service;

use App\Entity\Setting;
use Doctrine\ORM\EntityManager;

/**
 * Class SettingService
 * @package Cms\Service
 */
class SettingService
{
    public const EMAIL_FOR_COMPANY = 'emailForCompany';
    public const EMAIL_FOR_SHOP = 'emailForShop';
    public const EMAIL_FOR_KKT = 'emailForKkt';
    public const MIN_MONTH = 'minMonth';
    public const KKT_PRICE = 'kktPrice';
    public const FN_PRICE = 'fnPrice';
    public const KKT_SERVICE = 'kktService';
    public const FN_SERVICE = 'fnService';
    public const UMKA_LOGIN = 'umkaLogin';
    public const UMKA_PASSWORD = 'umkaPassword';
    public const KKT_LOCATION = 'kktLocation';
    public const CASHIER_LOGIN = 'cashierLogin';
    public const CASHIER_PASSWORD = 'cashierPassword';
    public const KKT_HOST = 'kktHost';
    public const UMKA_HOST = 'umkaHost';

    public const DEFAULT_SETTINGS = [
        'main' => [
            self::EMAIL_FOR_COMPANY => [
                'title'    => 'E-mail получателей при регистрации компании (разделитель `,`)',
                'type'     => 'text',
                'value'    => '',
                'required' => true,
            ],
            self::EMAIL_FOR_SHOP    => [
                'title'    => 'E-mail получателей при регистрации магазина (разделитель `,`)',
                'type'     => 'text',
                'value'    => '',
                'required' => true,
            ],
            self::EMAIL_FOR_KKT     => [
                'title'    => 'E-mail получателей при регистрации ККТ (разделитель `,`)',
                'type'     => 'text',
                'value'    => '',
                'required' => true,
            ],
            self::MIN_MONTH         => [
                'title'    => 'Минимальное кол-во месяцев для заказа',
                'type'     => 'number',
                'value'    => '',
                'required' => true,
            ],
            self::KKT_PRICE         => [
                'title'    => 'Стоимость аренды 1 кассы в месяц',
                'type'     => 'number',
                'value'    => '',
                'required' => true,
            ],
            self::FN_PRICE          => [
                'title'    => 'Стоимость ФН',
                'type'     => 'number',
                'value'    => '',
                'required' => true,
            ],
            self::KKT_SERVICE       => [
                'title'    => 'Название услуги (для счета)',
                'type'     => 'text',
                'value'    => '',
                'required' => true,
            ],
            self::FN_SERVICE        => [
                'title'    => 'Название товара ФН',
                'type'     => 'text',
                'value'    => '',
                'required' => true,
            ],
            self::UMKA_HOST        => [
                'title'    => 'Хост умки',
                'type'     => 'text',
                'value'    => '',
                'required' => true,
            ],
            self::UMKA_LOGIN        => [
                'title'    => 'Логин умки',
                'type'     => 'text',
                'value'    => '',
                'required' => true,
            ],
            self::UMKA_PASSWORD     => [
                'title'    => 'Пароль умки',
                'type'     => 'password',
                'value'    => '',
                'required' => true,
            ],
            self::KKT_LOCATION     => [
                'title'    => 'Место установки кассы',
                'type'     => 'text',
                'value'    => '',
                'required' => true,
            ],
            self::KKT_HOST        => [
                'title'    => 'Хост кассы',
                'type'     => 'text',
                'value'    => '',
                'required' => true,
            ],
            self::CASHIER_LOGIN        => [
                'title'    => 'Логин кассира (тел)',
                'type'     => 'text',
                'value'    => '',
                'required' => true,
            ],
            self::CASHIER_PASSWORD     => [
                'title'    => 'Пароль кассира',
                'type'     => 'password',
                'value'    => '',
                'required' => true,
            ],
        ],
    ];

    private $entityManager;

    /**
     * SiteService constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function updateDefaultParams(): void
    {
//        foreach ($site->getSiteSettings() as $key => $setting) {
//            var_dump($setting);
//        }

        $currentSettings = $this->entityManager->getRepository(Setting::class)->createQueryBuilder('s', 's.param')->getQuery()->getResult();
        foreach (self::DEFAULT_SETTINGS as $group => $items) {
            foreach ($items as $key => $value) {
                if (!isset($currentSettings[$key])) {
                    $set = new Setting();
                    $set->setGroup($group);
                    $set->setParam($key);
                    $set->setValue($value['value']);

                    $this->entityManager->persist($set);
                }
            }
        }

        $this->entityManager->flush();
    }
}
