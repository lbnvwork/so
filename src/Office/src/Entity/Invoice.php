<?php

namespace Office\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Invoice
 *
 * @ORM\Table(name="invoice", indexes={@ORM\Index(name="company_id", columns={"company_id"}), @ORM\Index(name="user_id", columns={"user_id"})})
 * @ORM\Entity
 */
class Invoice
{
    public const DRAFT = 0;
    public const ACCEPT = 1;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", precision=0, scale=0, nullable=false, options={"unsigned"=true}, unique=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="smallint", precision=0, scale=0, nullable=false, unique=false)
     */
    private $status;

    /**
     * @var int
     *
     * @ORM\Column(name="sum", type="integer", precision=0, scale=0, nullable=false, unique=false)
     */
    private $sum;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", precision=0, scale=0, nullable=false, options={"default"="CURRENT_TIMESTAMP"}, unique=false)
     */
    private $date = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date_accept", type="datetime", precision=0, scale=0, nullable=true, unique=false)
     */
    private $dateAccept;

    /**
     * @var string|null
     *
     * @ORM\Column(name="number", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $number;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date_update", type="datetime", precision=0, scale=0, nullable=true, unique=false)
     */
    private $dateUpdate;

    /**
     * @var \Office\Entity\Company
     *
     * @ORM\ManyToOne(targetEntity="Office\Entity\Company")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $company;

    /**
     * @var \Auth\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Auth\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $user;

    /**
     * @var \Auth\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Auth\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="updater_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $updater;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Office\Entity\InvoiceItem", mappedBy="invoice", cascade={"remove"})
     */
    private $item;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->item = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set status.
     *
     * @param int $status
     *
     * @return Invoice
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set sum.
     *
     * @param int $sum
     *
     * @return Invoice
     */
    public function setSum($sum)
    {
        $this->sum = $sum;

        return $this;
    }

    /**
     * Get sum.
     *
     * @return int
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Invoice
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set dateAccept.
     *
     * @param \DateTime|null $dateAccept
     *
     * @return Invoice
     */
    public function setDateAccept($dateAccept = null)
    {
        $this->dateAccept = $dateAccept;

        return $this;
    }

    /**
     * Get dateAccept.
     *
     * @return \DateTime|null
     */
    public function getDateAccept()
    {
        return $this->dateAccept;
    }

    /**
     * Set number.
     *
     * @param string|null $number
     *
     * @return Invoice
     */
    public function setNumber($number = null)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number.
     *
     * @return string|null
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set dateUpdate.
     *
     * @param \DateTime|null $dateUpdate
     *
     * @return Invoice
     */
    public function setDateUpdate($dateUpdate = null)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate.
     *
     * @return \DateTime|null
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * Set company.
     *
     * @param \Office\Entity\Company|null $company
     *
     * @return Invoice
     */
    public function setCompany(\Office\Entity\Company $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company.
     *
     * @return \Office\Entity\Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set user.
     *
     * @param \Auth\Entity\User|null $user
     *
     * @return Invoice
     */
    public function setUser(\Auth\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \Auth\Entity\User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set updater.
     *
     * @param \Auth\Entity\User|null $updater
     *
     * @return Invoice
     */
    public function setUpdater(\Auth\Entity\User $updater = null)
    {
        $this->updater = $updater;

        return $this;
    }

    /**
     * Get updater.
     *
     * @return \Auth\Entity\User|null
     */
    public function getUpdater()
    {
        return $this->updater;
    }

    /**
     * Add item.
     *
     * @param \Office\Entity\InvoiceItem $item
     *
     * @return Invoice
     */
    public function addItem(\Office\Entity\InvoiceItem $item)
    {
        $this->item[] = $item;

        return $this;
    }

    /**
     * Remove item.
     *
     * @param \Office\Entity\InvoiceItem $item
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeItem(\Office\Entity\InvoiceItem $item)
    {
        return $this->item->removeElement($item);
    }

    /**
     * Get item.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItem()
    {
        return $this->item;
    }
}
