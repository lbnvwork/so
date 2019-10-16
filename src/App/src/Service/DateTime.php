<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 14.02.19
 * Time: 13:27
 */

namespace App\Service;

use DateTimeZone;

/**
 * Class DateTime
 *
 * @package App\Service
 */
class DateTime extends \DateTime
{
    /**
     * DateTime constructor.
     *
     * @param string $time
     * @param DateTimeZone|null $timezone
     *
     * @throws \Exception
     */
    public function __construct(string $time = 'now', DateTimeZone $timezone = null)
    {
        parent::__construct($time, $timezone);
        if (defined('CURRENT_DATE')) {
            $this->modify(CURRENT_DATE);
            if ($time !== 'now') {
                $this->modify($time);
            }
        }
    }

    /**
     * Добавлет календарный месяц к текущей дате
     *
     * @param int $months
     *
     * @return $this
     */
    public function addMonths(int $months): self
    {
        $origDay = $this->format('d');
        $this->modify('+'.$months.' months');
        while ($this->format('d') < $origDay && $this->format('d') < 5) {
            $this->modify('-1 day');
        }

        return $this;
    }

    /**
     * Вычитает календарный месяц
     *
     * @param int $months
     *
     * @return DateTime
     */
    public function subMonths(int $months): self
    {
        $origDay = $this->format('d');
        $this->modify('-'.$months.' months');
        if ($this->format('d') === $origDay) {
            return $this;
        }

        while ($this->format('d') > $origDay || $this->format('d') < 5) {
            $this->modify('-1 day');
        }

        return $this;
    }
}
