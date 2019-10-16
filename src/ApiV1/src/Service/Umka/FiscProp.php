<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 04.02.18
 * Time: 13:53
 */

namespace ApiV1\Service\Umka;

/**
 * Class FiscProp
 * @package Umka
 */
class FiscProp
{
    /**
     * Тег
     * @var int
     */
    protected $tag;
    /**
     * Значение
     * @var null|string|int
     */
    protected $value;
    /**
     * Выводить на печать
     * @var null|int
     */
    protected $printable;
    /**
     * Вложенные свойства
     * @var FiscProp[]
     */
    protected $fiscprops;

    /**
     * FiscProp constructor.
     * @param string $_tag
     * @param null|string|int $_value
     * @param array $_fiscprops
     */
    public function __construct($_tag, $_value = null, $_fiscprops = [])
    {
        $this->tag = $_tag;
        $this->value = $_value;
        $this->fiscprops = $_fiscprops;
    }

    /**
     * @return int
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param int $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    /**
     * @return int|null
     */
    public function getPrintable()
    {
        return $this->printable;
    }

    /**
     * @param int|null $printable
     */
    public function setPrintable($printable)
    {
        $this->printable = $printable;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $_value
     */
    public function setValue($_value)
    {
        $this->value = $_value;
    }

    /**
     * @return array
     */
    public function getFiscprops()
    {
        return $this->fiscprops;
    }

    /**
     * @param array $_fiscprops
     */
    public function setFiscprops($_fiscprops)
    {
        $this->fiscprops = $_fiscprops;
    }

    /**
     * @param $_prop
     */
    public function addFiscprop($_prop)
    {
        $this->fiscprops[] = $_prop;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = ['tag' => $this->tag];
        if ($this->printable !== null) {
            $data['printable'] = $this->printable;
        }
        if ($this->value !== null) {
            $data['value'] = $this->value;
        }
        if (!empty($this->fiscprops)) {
            foreach ($this->fiscprops as $fiscprop) {
                $data['fiscprops'][] = $fiscprop->toArray();
            }
        }

        return $data;
    }
}
