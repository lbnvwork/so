<?php


namespace ApiV1\Service\Check;

/**
 * Class Tax
 *
 * @package ApiV1\Service\Check
 */
class Tax
{
    /**
     * Возвращает код налога для ККТ
     *
     * @param string $tax
     *
     * @return int
     */
    public static function getTaxCode(string $tax): int
    {
        $code = 6;
        if ($tax === 'vat18' || $tax === 'vat20') {
            $code = 1;
        } elseif ($tax === 'vat10') {
            $code = 2;
        } elseif ($tax === 'vat118' || $tax === 'vat120') {
            $code = 3;
        } elseif ($tax === 'vat110') {
            $code = 4;
        } elseif ($tax === 'vat0') {
            $code = 5;
        }

        return $code;
    }
}
