<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 30.01.18
 * Time: 22:20
 */

namespace ApiV1\Service\Umka;

class Cashbox
{
    protected $data;

    public function __construct($_data)
    {
        if (empty($_data)) {
            throw new \InvalidArgumentException('Data not set!');
        }

        $this->setData($_data);
    }

    public function setData($_data)
    {
        $this->data = $_data;
    }

    /**
     * @param string $_name
     * @return mixed
     */
    public function getParam($_name)
    {
        return $this->data[$_name];
    }

    public function isFiscalized()
    {
        return $this->bitCheck($this->data['flags'], 0);
    }

    public function isOpenCycle()
    {
//        return $this->bitCheck($this->data['flags'], 1);
        return (bool)$this->data['fsStatus']['cycleIsOpen'];
    }

    public function isOpenCashBox()
    {
        return $this->bitCheck($this->data['flags'], 2);
    }

    public function isIssetPaper()
    {
        return $this->bitCheck($this->data['flags'], 3);
    }

    public function isOpenCap()
    {
        return $this->bitCheck($this->data['flags'], 5);
    }

    public function isActiveFN()
    {
        return $this->bitCheck($this->data['flags'], 6);
    }

    public function isUseBattery()
    {
        return $this->bitCheck($this->data['flags'], 7);
    }

    protected function bitCheck($num, $bit)
    {
        return (($num >> $bit) % 2 != 0);
    }
}
