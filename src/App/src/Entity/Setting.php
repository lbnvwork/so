<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Setting
 *
 * @ORM\Table(name="setting")})
 * @ORM\Entity
 */
class Setting
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", precision=0, scale=0, nullable=false, options={"unsigned"=true}, unique=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="param", type="string", length=32, precision=0, scale=0, nullable=false, unique=false)
     */
    private $param;

    /**
     * @var string|null
     *
     * @ORM\Column(name="value", type="string", length=255, precision=0, scale=0, nullable=true, unique=false)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="group_name", type="string", length=16, precision=0, scale=0, nullable=false, unique=false)
     */
    private $groupName;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set param.
     *
     * @param string $param
     *
     * @return Setting
     */
    public function setParam($param)
    {
        $this->param = $param;

        return $this;
    }

    /**
     * Get param.
     *
     * @return string
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return Setting
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set group.
     *
     * @param string $group
     *
     * @return Setting
     */
    public function setGroup($group)
    {
        $this->groupName = $group;

        return $this;
    }

    /**
     * Get group.
     *
     * @return string
     */
    public function getGroup()
    {
        return $this->groupName;
    }

    /**
     * @return null|string
     */
    public function __toString()
    {
        return $this->getValue();
    }
}
