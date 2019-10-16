<?php

namespace Office\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MoneyHistory
 * @ORM\Table(
 *     name="money_history",
 *     indexes={
 *     @ORM\Index(name="invoice_id", columns={"invoice_id"}),
 *     @ORM\Index(name="user_id", columns={"user_id"}),
 *     @ORM\Index(name="company_id", columns={"company_id"})
 *      }
 *     )
 *
 * @ORM\Entity
 */
class MoneyHistory
{
    public const TYPE_IN = 0;
    public const TYPE_OUT = 1;
    public const TYPES = [
        self::TYPE_IN  => 'Зачисление',
        self::TYPE_OUT => 'Списание',
    ];

    /**
     * @var int
     * @ORM\Column(name="id", type="integer", precision=0, scale=0, nullable=false, options={"unsigned"=true}, unique=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(name="type", type="smallint", precision=0, scale=0, nullable=false, unique=false)
     */
    private $type;

    /**
     * @var int
     * @ORM\Column(name="sum", type="integer", precision=0, scale=0, nullable=false, unique=false)
     */
    private $sum;

    /**
     * @var \DateTime
     * @ORM\Column(name="datetime", type="datetime", precision=0, scale=0, nullable=false, options={"default"="CURRENT_TIMESTAMP"}, unique=false)
     */
    private $datetime = 'CURRENT_TIMESTAMP';

    /**
     * @var string|null
     * @ORM\Column(name="title", type="string", length=1024, precision=0, scale=0, nullable=true, unique=false)
     */
    private $title;

    /**
     * @var \Office\Entity\Invoice
     * @ORM\ManyToOne(targetEntity="Office\Entity\Invoice")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="invoice_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $invoice;

    /**
     * @var \Office\Entity\Company
     * @ORM\ManyToOne(targetEntity="Office\Entity\Company")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $company;

    /**
     * @var \Auth\Entity\User
     * @ORM\ManyToOne(targetEntity="Auth\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $user;

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
     * Set type.
     *
     * @param int $type
     *
     * @return MoneyHistory
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set sum.
     *
     * @param int $sum
     *
     * @return MoneyHistory
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
     * Set datetime.
     *
     * @param \DateTime $datetime
     *
     * @return MoneyHistory
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * Get datetime.
     *
     * @return \DateTime
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return MoneyHistory
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set invoice.
     *
     * @param \Office\Entity\Invoice|null $invoice
     *
     * @return MoneyHistory
     */
    public function setInvoice(\Office\Entity\Invoice $invoice = null)
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * Get invoice.
     *
     * @return \Office\Entity\Invoice|null
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * Set company.
     *
     * @param \Office\Entity\Company|null $company
     *
     * @return MoneyHistory
     */
    public function setCompany(\Office\Entity\Company $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company.
     *
     * @return \Office\Entity\Company|null
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
     * @return MoneyHistory
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
}
