<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 07.02.18
 * Time: 21:00
 */

namespace ApiV1\Service\Umka\Props;

use ApiV1\Service\Umka\ExtendFiscProp;

/**
 * Предмет рассчета
 * @package Umka\Props
 */
class SubjectCalculation extends ExtendFiscProp
{
    const TAG = 1059;

    public function __construct($_fiscprops = [])
    {
        parent::__construct(null, $_fiscprops);
    }
}
