<?php

namespace Office\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReferralPayment
 *
 * @ORM\Table(
 *     name="referral_payment",
 *     indexes={
 *     @ORM\Index(name="user_id", columns={"user_id"}),
 *     @ORM\Index(name="from_user_id", columns={"from_user_id"}),
 *     @ORM\Index(name="company_id", columns={"company_id"}),
 *     @ORM\Index(name="pay", columns={"pay"})
 *      }
 *     )
 * @ORM\Entity
 */
class ReferralPayment
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
     * @var int|null
     *
     * @ORM\Column(name="sum", type="integer", precision=0, scale=0, nullable=true, options={"unsigned"=true}, unique=false)
     */
    private $sum;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datetime", type="datetime", precision=0, scale=0, nullable=false, unique=false)
     */
    private $datetime;

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
     *   @ORM\JoinColumn(name="from_user_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $fromUser;

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
     * @var int
     *
     * @ORM\Column(name="pay", type="smallint", precision=1, scale=0, nullable=true, options={"default"="0"}, unique=false)
     */
    private $pay;


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
     * Set sum.
     *
     * @param int|null $sum
     *
     * @return ReferralPayment
     */
    public function setSum($sum = null)
    {
        $this->sum = $sum;

        return $this;
    }

    /**
     * Get sum.
     *
     * @return int|null
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
     * @return ReferralPayment
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
     * Set user.
     *
     * @param \Auth\Entity\User|null $user
     *
     * @return ReferralPayment
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
     * Set fromUser.
     *
     * @param \Auth\Entity\User|null $fromUser
     *
     * @return ReferralPayment
     */
    public function setFromUser(\Auth\Entity\User $fromUser = null)
    {
        $this->fromUser = $fromUser;

        return $this;
    }

    /**
     * Get fromUser.
     *
     * @return \Auth\Entity\User|null
     */
    public function getFromUser()
    {
        return $this->fromUser;
    }

    /**
     * Set company.
     *
     * @param \Office\Entity\Company|null $company
     *
     * @return ReferralPayment
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
     * Get pay.
     *
     * @return int
     */
    public function getPay()
    {
        return $this->pay;
    }


    /**
     * Set pay.
     *
     * @param int|null $sum
     *
     * @return ReferralPayment
     */
    public function setPay($pay = 0)
    {
        $this->pay = $pay;

        return $this;
    }
}
