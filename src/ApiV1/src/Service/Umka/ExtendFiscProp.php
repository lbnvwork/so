<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 04.02.18
 * Time: 20:38
 */

namespace ApiV1\Service\Umka;

/**
 * Class ExtendFiscProp
 *
 * @package Umka
 */
class ExtendFiscProp extends FiscProp
{
    /**
     * ExtendFiscProp constructor.
     *
     * @param null $_value
     * @param array $_fiscprops
     */
    public function __construct($_value = null, $_fiscprops = [])
    {
        parent::__construct(static::TAG, $_value, $_fiscprops);
    }
}
