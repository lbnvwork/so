<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 05.07.18
 * Time: 11:44
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Service
 * @ORM\Table(name="service")})
 *
 * @ORM\Entity
 * @package App\Entity
 */
class Service
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer", precision=0, scale=0, nullable=false, options={"unsigned"=true}, unique=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=1024, precision=0, scale=0, nullable=false, unique=false)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(name="price", type="string", length=255, precision=0, scale=0, nullable=true, unique=false)
     */
    private $price;

    /**
     * @var string|null
     * @ORM\Column(name="measure", type="string", length=255, precision=0, scale=0, nullable=true, unique=false)
     */
    private $measure;

    /**
     * @var int
     * @ORM\Column(name="default_value", type="integer", precision=0, scale=0, nullable=false, options={"unsigned"=true}, unique=false)
     */
    private $defaultValue;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\ManyToMany(targetEntity="\Office\Entity\Tariff", mappedBy="service")
     */
    private $tariff;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tariff = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $_id
     *
     * @return $this;
     */
    public function setId($_id)
    {
        $this->id = $_id;

        return $this;
    }

    /**
     * @param string $_name
     *
     * @return $this;
     */
    public function setName($_name)
    {
        $this->name = $_name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set price.
     *
     * @param string|null $price
     *
     * @return Service
     */
    public function setPrice($price = null)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price.
     *
     * @return string|null
     */
    public function getPrice(): ?string
    {
        return $this->price;
    }

    /**
     * Set measure.
     *
     * @param string|null $measure
     *
     * @return Service
     */
    public function setMeasure($_measure)
    {
        $this->measure = $_measure;

        return $this;
    }

    /**
     * Get measure.
     *
     * @return string|null
     */
    public function getMeasure(): ?string
    {
        return $this->measure;
    }

    /**
     * Set defaultValue.
     *
     * @param int $defaultValue
     *
     * @return Service
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * Get defaultValue.
     *
     * @return int
     */
    public function getDefaultValue(): ?int
    {
        return $this->defaultValue;
    }

    /**
     * Add tariff.
     *
     * @param \Office\Entity\Tariff $tariff
     *
     * @return Service
     */
    public function addTariff(\Office\Entity\Tariff $tariff)
    {
        $this->tariff[] = $tariff;

        return $this;
    }

    /**
     * Remove tariff.
     *
     * @param \Office\Entity\Tariff $tariff
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeTariff(\Office\Entity\Tariff $tariff)
    {
        return $this->tariff->removeElement($tariff);
    }

    /**
     * Get tariff.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTariff()
    {
        return $this->tariff;
    }
}
