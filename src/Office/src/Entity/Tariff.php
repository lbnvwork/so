<?php

namespace Office\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tariff
 * @ORM\Table(name="tariff")
 *
 * @ORM\Entity
 */
class Tariff
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer", precision=0, scale=0, nullable=false, unique=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="title", type="string", length=100, precision=0, scale=0, nullable=false, options={"comment"="Заголовок"}, unique=false)
     */
    private $title;

    /**
     * @var string|null
     * @ORM\Column(name="description", type="text", length=65535, precision=0, scale=0, nullable=true, options={"comment"="Описание"}, unique=false)
     */
    private $description;

    /**
     * @var int
     * @ORM\Column(name="rent_cost", type="integer", precision=0, scale=0, nullable=false, options={"comment"="Стоимость аренды"}, unique=false)
     */
    private $rentCost;

    /**
     * @var float|null
     * @ORM\Column(name="turnover_percent", type="float", precision=10, scale=0, nullable=true, options={"comment"="Процент от оборота"}, unique=false)
     */
    private $turnoverPercent;

    /**
     * @var int|null
     * @ORM\Column(
     *     name="month_limit",
     *     type="integer",
     *     precision=0,
     *     scale=0,
     *     nullable=true,
     *     options={"comment"="Ограничение действия тарифа по количеству месяцев"},
     *     unique=false
     *     )
     */
    private $monthLimit;

    /**
     * @var int|null
     * @ORM\Column(
     *     name="month_count",
     *     type="integer",
     *     precision=0,
     *     scale=0,
     *     nullable=true,
     *     options={"comment"="Количество месяцев покупки тарифа как условие подключения"},
     *     unique=false
     *     )
     */
    private $monthCount;

    /**
     * @var int|null
     * @ORM\Column(name="fn_live_time", type="integer", precision=0, scale=0, nullable=true, options={"comment"="Количество месяцев для ФН"}, unique=false)
     */
    private $fnLiveTime;

    /**
     * @var int|null
     * @ORM\Column(name="max_turnover", type="integer", precision=0, scale=0, nullable=true, options={"comment"="Предельный размер оборота"}, unique=false)
     */
    private $maxTurnover;

    /**
     * @var int
     * @ORM\Column(name="sort", type="integer", precision=0, scale=0, nullable=false, unique=false)
     */
    private $sort = '0';

    /**
     * @var bool
     * @ORM\Column(name="is_popular", type="boolean", precision=0, scale=0, nullable=false, options={"comment"="Популярный тариф"}, unique=false)
     */
    private $isPopular;

    /**
     * @var bool
     * @ORM\Column(name="is_default", type="boolean", precision=0, scale=0, nullable=false, options={"comment"="Тариф по умолчанию"}, unique=false)
     */
    private $isDefault;

    /**
     * @var bool
     * @ORM\Column(name="is_beginner", type="boolean", precision=0, scale=0, nullable=false, options={"comment"="Только для новичков"}, unique=false)
     */
    private $isBeginner;

    /**
     * @var bool
     * @ORM\Column(name="is_promotime", type="boolean", nullable=false, options={"comment"="Первый месяц бесплатно"})
     */
    private $isPromotime;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\ManyToMany(targetEntity="App\Entity\Service", inversedBy="tariff")
     * @ORM\JoinTable(name="tariff_has_service",
     *   joinColumns={
     *     @ORM\JoinColumn(name="tariff_id", referencedColumnName="id", nullable=true)
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="service_id", referencedColumnName="id", nullable=true)
     *   }
     * )
     */
    private $service;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->service = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set title.
     *
     * @param string $title
     *
     * @return Tariff
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return Tariff
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set rentCost.
     *
     * @param int $rentCost
     *
     * @return Tariff
     */
    public function setRentCost($rentCost)
    {
        $this->rentCost = $rentCost;

        return $this;
    }

    /**
     * Get rentCost.
     *
     * @return int
     */
    public function getRentCost()
    {
        return $this->rentCost;
    }

    /**
     * Set turnoverPercent.
     *
     * @param float|null $turnoverPercent
     *
     * @return Tariff
     */
    public function setTurnoverPercent($turnoverPercent = null)
    {
        $this->turnoverPercent = $turnoverPercent;

        return $this;
    }

    /**
     * Get turnoverPercent.
     *
     * @return float|null
     */
    public function getTurnoverPercent()
    {
        return $this->turnoverPercent;
    }

    /**
     * Set monthLimit.
     *
     * @param int|null $monthLimit
     *
     * @return Tariff
     */
    public function setMonthLimit($monthLimit = null)
    {
        $this->monthLimit = $monthLimit;

        return $this;
    }

    /**
     * Get monthLimit.
     *
     * @return int|null
     */
    public function getMonthLimit()
    {
        return $this->monthLimit;
    }

    /**
     * Set monthCount.
     *
     * @param int|null $monthCount
     *
     * @return Tariff
     */
    public function setMonthCount($monthCount = null)
    {
        $this->monthCount = $monthCount;

        return $this;
    }

    /**
     * Get monthCount.
     *
     * @return int|null
     */
    public function getMonthCount()
    {
        return $this->monthCount;
    }

    /**
     * Set fnLiveTime.
     *
     * @param int|null $fnLiveTime
     *
     * @return Tariff
     */
    public function setFnliveTime($fnLiveTime = null)
    {
        $this->fnLiveTime = $fnLiveTime;

        return $this;
    }

    /**
     * Get fnLiveTime.
     *
     * @return int|null
     */
    public function getFnliveTime()
    {
        return $this->fnLiveTime;
    }

    /**
     * Set maxTurnover.
     *
     * @param int|null $maxTurnover
     *
     * @return Tariff
     */
    public function setMaxTurnover($maxTurnover = null)
    {
        $this->maxTurnover = $maxTurnover;

        return $this;
    }

    /**
     * Get maxTurnover.
     *
     * @return int|null
     */
    public function getMaxTurnover()
    {
        return $this->maxTurnover;
    }

    /**
     * Set sort.
     *
     * @param int $sort
     *
     * @return Tariff
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Get sort.
     *
     * @return int
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Set isPopular.
     *
     * @param bool $isPopular
     *
     * @return Tariff
     */
    public function setIsPopular($isPopular)
    {
        $this->isPopular = $isPopular;

        return $this;
    }

    /**
     * Get isPopular.
     *
     * @return bool
     */
    public function isPopular()
    {
        return $this->isPopular;
    }

    /**
     * Set isDefault.
     *
     * @param bool $isPopular
     *
     * @return Tariff
     */
    public function setIsDefault($isPopular)
    {
        $this->isDefault = $isPopular;

        return $this;
    }

    /**
     * Get isDefault.
     *
     * @return bool
     */
    public function isDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set isBeginner.
     *
     * @param bool $isBeginner
     *
     * @return Tariff
     */
    public function setIsBeginner($isBeginner)
    {
        $this->isBeginner = $isBeginner;

        return $this;
    }

    /**
     * Get isBeginner.
     *
     * @return bool
     */
    public function getIsBeginner()
    {
        return $this->isBeginner;
    }

    /**
     * Set isPromotime.
     *
     * @param bool $isPromotime
     *
     * @return Tariff
     */
    public function setIsPromotime($isPromotime)
    {
        $this->isPromotime = $isPromotime;

        return $this;
    }

    /**
     * Get isPromotime.
     *
     * @return bool
     */
    public function getIsPromotime()
    {
        return $this->isPromotime;
    }

    /**
     * Add service.
     *
     * @param \App\Entity\Service $service
     *
     * @return Tariff
     */
    public function addService(\App\Entity\Service $service)
    {
        $this->service[] = $service;

        return $this;
    }

    /**
     * Remove service.
     *
     * @param \App\Entity\Service $service
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeService(\App\Entity\Service $service)
    {
        return $this->service->removeElement($service);
    }

    /**
     * Get service.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getService()
    {
        return $this->service;
    }
}
