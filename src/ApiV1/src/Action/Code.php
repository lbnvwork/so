<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 10.04.18
 * Time: 11:36
 */

namespace ApiV1\Action;

/**
 * Коды ответов сервера
 *
 * @package ApiV1\Action
 */
class Code
{
    public const INCORRECT_LOGIN = 1;
    public const USE_OLD_TOKEN = 2;
    public const USE_NEW_TOKEN = 3;
    public const INCORRECT_TOKEN = 4;
    public const INCORRECT_SHOP = 5;
    public const INCORRECT_OPERATION = 6;
    public const NOT_FOUND_KKT = 7;
    public const INCORRECT_PROCESSING_ID = 8;
    public const INCORECT_DATA = 9;

    /** Таблица сообщений */
    public const MESSAGES = [
        self::INCORRECT_LOGIN => 'Неверный логин или пароль',
        self::USE_OLD_TOKEN => 'Используется старый токен',
        self::USE_NEW_TOKEN => 'Сгенерирован новый токен',
        self::INCORRECT_TOKEN => 'Не валидный токен',
        self::INCORRECT_SHOP => 'Некорректный магазин',
        self::INCORRECT_OPERATION => 'Некорректная операция',
        self::NOT_FOUND_KKT => 'Нет доступных ККТ',
        self::INCORRECT_PROCESSING_ID => 'Некорректный id чека',
        self::INCORECT_DATA => 'Некорректные данные в чеке',
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
