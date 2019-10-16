<?php

namespace Office\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Company
 * @ORM\Table(name="company", indexes={@ORM\Index(name="IDX_4FBF094F7356E2B7", columns={"ofd_id"})})
 *
 * @ORM\Entity
 */
class Company
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
     * @ORM\Column(name="title", type="string", length=255, precision=0, scale=0, nullable=false, unique=false)
     */
    private $title;

    /**
     * @var string|null
     * @ORM\Column(name="inn", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $inn;

    /**
     * @var string|null
     * @ORM\Column(name="kpp", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $kpp;

    /**
     * @var string|null
     * @ORM\Column(name="raw_data", type="text", length=65535, precision=0, scale=0, nullable=true, unique=false)
     */
    private $rawData;

    /**
     * @var string|null
     * @ORM\Column(name="ogrn_ip", type="string", length=64, precision=0, scale=0, nullable=true, unique=false)
     */
    private $ogrnIp;

    /**
     * @var string|null
     * @ORM\Column(name="ogrn", type="string", length=64, precision=0, scale=0, nullable=true, unique=false)
     */
    private $ogrn;

    /**
     * @var string|null
     * @ORM\Column(name="type", type="string", length=256, precision=0, scale=0, nullable=true, unique=false)
     */
    private $type;

    /**
     * @var int
     * @ORM\Column(name="org_type", type="smallint", precision=0, scale=0, nullable=false, unique=false)
     */
    private $orgType = '0';

    /**
     * @var int
     * @ORM\Column(name="nalog_system", type="smallint", precision=0, scale=0, nullable=false, unique=false)
     */
    private $nalogSystem = '0';

    /**
     * @var string|null
     * @ORM\Column(name="ip_last_name", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $ipLastName;

    /**
     * @var string|null
     * @ORM\Column(name="ip_first_name", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $ipFirstName;

    /**
     * @var string|null
     * @ORM\Column(name="ip_middle_name", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $ipMiddleName;

    /**
     * @var string|null
     * @ORM\Column(name="address", type="string", length=512, precision=0, scale=0, nullable=true, unique=false)
     */
    private $address;

    /**
     * @var string|null
     * @ORM\Column(name="company_phone", type="string", length=64, precision=0, scale=0, nullable=true, unique=false)
     */
    private $companyPhone;

    /**
     * @var string|null
     * @ORM\Column(name="company_email", type="string", length=64, precision=0, scale=0, nullable=true, unique=false)
     */
    private $companyEmail;

    /**
     * @var string|null
     * @ORM\Column(name="director_last_name", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $directorLastName;

    /**
     * @var string|null
     * @ORM\Column(name="director_first_name", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $directorFirstName;

    /**
     * @var string|null
     * @ORM\Column(name="director_middle_name", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $directorMiddleName;

    /**
     * @var string|null
     * @ORM\Column(name="company_check_email", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $companyCheckEmail;

    /**
     * @var bool
     * @ORM\Column(name="is_enabled", type="boolean", precision=0, scale=0, nullable=false, unique=false)
     */
    private $isEnabled = '0';

    /**
     * @var bool
     * @ORM\Column(name="is_deleted", type="boolean", precision=0, scale=0, nullable=false, unique=false)
     */
    private $isDeleted = '0';

    /**
     * @var int
     * @ORM\Column(name="balance", type="integer", precision=0, scale=0, nullable=false, unique=false)
     */
    private $balance = '0';

    /**
     * @var \DateTime
     * @ORM\Column(name="date", type="datetime", precision=0, scale=0, nullable=false, options={"default"="CURRENT_TIMESTAMP"}, unique=false)
     */
    private $date = 'CURRENT_TIMESTAMP';

    /**
     * @var \Office\Entity\Ofd
     * @ORM\ManyToOne(targetEntity="Office\Entity\Ofd")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ofd_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $ofd;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\ManyToMany(targetEntity="Auth\Entity\User", mappedBy="company", indexBy="id")
     */
    private $user;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Office\Entity\Shop", mappedBy="company", indexBy="id", cascade={"remove"})
     */
    private $shop;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Office\Entity\ReferralPayment", mappedBy="company", indexBy="id", cascade={"remove"})
     */
    private $referral;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tariff = new \Doctrine\Common\Collections\ArrayCollection();
        $this->user = new \Doctrine\Common\Collections\ArrayCollection();
        $this->shop = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setDate(new \DateTime());
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
     * @return Company
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
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Company
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
     * Set inn.
     *
     * @param string|null $inn
     *
     * @return Company
     */
    public function setInn($inn = null)
    {
        $this->inn = $inn;

        return $this;
    }

    /**
     * Get inn.
     *
     * @return string|null
     */
    public function getInn()
    {
        return $this->inn;
    }

    /**
     * Set kpp.
     *
     * @param string|null $kpp
     *
     * @return Company
     */
    public function setKpp($kpp = null)
    {
        $this->kpp = $kpp;

        return $this;
    }

    /**
     * Get kpp.
     *
     * @return string|null
     */
    public function getKpp()
    {
        return $this->kpp;
    }

    /**
     * Set rawData.
     *
     * @param string|null $rawData
     *
     * @return Company
     */
    public function setRawData($rawData = null)
    {
        $this->rawData = $rawData;

        return $this;
    }

    /**
     * Get rawData.
     *
     * @return string|null
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    /**
     * Set ogrnIp.
     *
     * @param string|null $ogrnIp
     *
     * @return Company
     */
    public function setOgrnIp($ogrnIp = null)
    {
        $this->ogrnIp = $ogrnIp;

        return $this;
    }

    /**
     * Get ogrnIp.
     *
     * @return string|null
     */
    public function getOgrnIp()
    {
        return $this->ogrnIp;
    }

    /**
     * Set ogrn.
     *
     * @param string|null $ogrn
     *
     * @return Company
     */
    public function setOgrn($ogrn = null)
    {
        $this->ogrn = $ogrn;

        return $this;
    }

    /**
     * Get ogrn.
     *
     * @return string|null
     */
    public function getOgrn()
    {
        return $this->ogrn;
    }

    /**
     * Set type.
     *
     * @param string|null $type
     *
     * @return Company
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set orgType.
     *
     * @param int $orgType
     *
     * @return Company
     */
    public function setOrgType($orgType)
    {
        $this->orgType = $orgType;

        return $this;
    }

    /**
     * Get orgType.
     *
     * @return int
     */
    public function getOrgType()
    {
        return $this->orgType;
    }

    /**
     * Set nalogSystem.
     *
     * @param int $nalogSystem
     *
     * @return Company
     */
    public function setNalogSystem($nalogSystem)
    {
        $this->nalogSystem = $nalogSystem;

        return $this;
    }

    /**
     * Get nalogSystem.
     *
     * @return int
     */
    public function getNalogSystem()
    {
        return $this->nalogSystem;
    }

    /**
     * Set ipLastName.
     *
     * @param string|null $ipLastName
     *
     * @return Company
     */
    public function setIpLastName($ipLastName = null)
    {
        $this->ipLastName = $ipLastName;

        return $this;
    }

    /**
     * Get ipLastName.
     *
     * @return string|null
     */
    public function getIpLastName()
    {
        return $this->ipLastName;
    }

    /**
     * Set ipFirstName.
     *
     * @param string|null $ipFirstName
     *
     * @return Company
     */
    public function setIpFirstName($ipFirstName = null)
    {
        $this->ipFirstName = $ipFirstName;

        return $this;
    }

    /**
     * Get ipFirstName.
     *
     * @return string|null
     */
    public function getIpFirstName()
    {
        return $this->ipFirstName;
    }

    /**
     * Set ipMiddleName.
     *
     * @param string|null $ipMiddleName
     *
     * @return Company
     */
    public function setIpMiddleName($ipMiddleName = null)
    {
        $this->ipMiddleName = $ipMiddleName;

        return $this;
    }

    /**
     * Get ipMiddleName.
     *
     * @return string|null
     */
    public function getIpMiddleName()
    {
        return $this->ipMiddleName;
    }

    /**
     * Set address.
     *
     * @param string|null $address
     *
     * @return Company
     */
    public function setAddress($address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address.
     *
     * @return string|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set companyPhone.
     *
     * @param string|null $companyPhone
     *
     * @return Company
     */
    public function setCompanyPhone($companyPhone = null)
    {
        $this->companyPhone = $companyPhone;

        return $this;
    }

    /**
     * Get companyPhone.
     *
     * @return string|null
     */
    public function getCompanyPhone()
    {
        return $this->companyPhone;
    }

    /**
     * Set companyEmail.
     *
     * @param string|null $companyEmail
     *
     * @return Company
     */
    public function setCompanyEmail($companyEmail = null)
    {
        $this->companyEmail = $companyEmail;

        return $this;
    }

    /**
     * Get companyEmail.
     *
     * @return string|null
     */
    public function getCompanyEmail()
    {
        return $this->companyEmail;
    }

    /**
     * Set directorLastName.
     *
     * @param string|null $directorLastName
     *
     * @return Company
     */
    public function setDirectorLastName($directorLastName = null)
    {
        $this->directorLastName = $directorLastName;

        return $this;
    }

    /**
     * Get directorLastName.
     *
     * @return string|null
     */
    public function getDirectorLastName()
    {
        return $this->directorLastName;
    }

    /**
     * Set directorFirstName.
     *
     * @param string|null $directorFirstName
     *
     * @return Company
     */
    public function setDirectorFirstName($directorFirstName = null)
    {
        $this->directorFirstName = $directorFirstName;

        return $this;
    }

    /**
     * Get directorFirstName.
     *
     * @return string|null
     */
    public function getDirectorFirstName()
    {
        return $this->directorFirstName;
    }

    /**
     * Set directorMiddleName.
     *
     * @param string|null $directorMiddleName
     *
     * @return Company
     */
    public function setDirectorMiddleName($directorMiddleName = null)
    {
        $this->directorMiddleName = $directorMiddleName;

        return $this;
    }

    /**
     * Get directorMiddleName.
     *
     * @return string|null
     */
    public function getDirectorMiddleName()
    {
        return $this->directorMiddleName;
    }

    /**
     * Set companyCheckEmail.
     *
     * @param string|null $companyCheckEmail
     *
     * @return Company
     */
    public function setCompanyCheckEmail($companyCheckEmail = null)
    {
        $this->companyCheckEmail = $companyCheckEmail;

        return $this;
    }

    /**
     * Get companyCheckEmail.
     *
     * @return string|null
     */
    public function getCompanyCheckEmail()
    {
        return $this->companyCheckEmail;
    }

    /**
     * Set isEnabled.
     *
     * @param bool $isEnabled
     *
     * @return Company
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * Get isEnabled.
     *
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Set isDeleted.
     *
     * @param bool $isDeleted
     *
     * @return Company
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted.
     *
     * @return bool
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set balance.
     *
     * @param int $balance
     *
     * @return Company
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * Get balance.
     *
     * @return int
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * Set ofd.
     *
     * @param \Office\Entity\Ofd|null $ofd
     *
     * @return Company
     */
    public function setOfd(\Office\Entity\Ofd $ofd = null)
    {
        $this->ofd = $ofd;

        return $this;
    }

    /**
     * Get ofd.
     *
     * @return \Office\Entity\Ofd|null
     */
    public function getOfd()
    {
        return $this->ofd;
    }

    /**
     * Add user.
     *
     * @param \Auth\Entity\User $user
     *
     * @return Company
     */
    public function addUser(\Auth\Entity\User $user)
    {
        $this->user[] = $user;

        return $this;
    }

    /**
     * Remove user.
     *
     * @param \Auth\Entity\User $user
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeUser(\Auth\Entity\User $user)
    {
        return $this->user->removeElement($user);
    }

    /**
     * Get user.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add shop.
     *
     * @param \Office\Entity\Shop $shop
     *
     * @return Company
     */
    public function addShop(\Office\Entity\Shop $shop)
    {
        $this->shop[] = $shop;

        return $this;
    }

    /**
     * Remove shop.
     *
     * @param \Office\Entity\Shop $shop
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeShop(\Office\Entity\Shop $shop)
    {
        return $this->shop->removeElement($shop);
    }

    /**
     * Get shop.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @param int $sum
     *
     * @return $this
     */
    public function addBalance(int $sum)
    {
        $this->balance += $sum;

        return $this;
    }

    /**
     * Get default company title
     *
     * @return string
     */
    public function getDefaultTitle()
    {
        if (!empty($this->getTitle())) {
            return $this->getTitle();
        } elseif (!empty($this->getIpLastName().$this->getIpFirstName().$this->getIpMiddleName())) {
            return 'ИП '.$this->getIpLastName().' '.$this->getIpFirstName().' '.$this->getIpMiddleName();
        } elseif (!empty($this->getDirectorLastName().$this->getDirectorFirstName().$this->getDirectorMiddleName())) {
            return $this->getDirectorLastName().' '.$this->getDirectorFirstName().' '.$this->getDirectorMiddleName();
        } else {
            return '';
        }
    }
}
