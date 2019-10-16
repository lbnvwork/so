<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 10.04.18
 * Time: 11:36
 */

namespace ApiAtolV1\Action;

/**
 * Коды ответов сервера
 *
 * @package ApiAtolV1\Action
 */
class Code
{
    public const INCORRECT_LOGIN = 19;
    public const USE_OLD_TOKEN = 1;
    public const OLD_TOKEN = 6;
    public const USE_NEW_TOKEN = 0;
    public const INCORRECT_TOKEN = 4;
    public const INCORRECT_SHOP = 5;
    public const INCORRECT_OPERATION = 3;
    public const NOT_FOUND_KKT = 7;
    public const INCORRECT_PROCESSING_ID = 8;
    public const INCORECT_DATA = 1;
    public const ISSET_EXTERNALID_SHOP = 10;
    public const PROCESSIG = 16;
    public const INCORRECT_SUM = 3803;

    /** Таблица сообщений */
    public const MESSAGES = [
        self::INCORRECT_LOGIN => 'Неверный логин или пароль',
        self::USE_OLD_TOKEN => 'Используется старый токен',
        self::USE_NEW_TOKEN => 'Сгенерирован новый токен',
        self::INCORRECT_TOKEN => 'Невалидный токен',
        self::INCORRECT_SHOP => 'Некорректный магазин',
        self::INCORRECT_OPERATION => 'Некорректная операция',
        self::NOT_FOUND_KKT => 'Нет доступных ККТ',
        self::INCORRECT_PROCESSING_ID => 'Некорректный id чека',
        self::INCORECT_DATA => 'Некорректные данные в чеке',
        self::ISSET_EXTERNALID_SHOP => 'В системе существует чек с external_id и group_code',
        self::OLD_TOKEN => 'Переданный токен не активен',
        self::PROCESSIG => 'Нет информации, попробуйте позднее',
        self::INCORRECT_SUM => 'Сумма чека не равняется сумме всех позиций',
    ];

    /**
     * @param int $code
     *
     * @return string
     */
    public static function getMessage(int $code): string
    {
        return self::MESSAGES[$code];
    }
}
