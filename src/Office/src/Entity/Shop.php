<?php

namespace Office\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Shop
 * @ORM\Table(name="shop", indexes={@ORM\Index(name="company_id", columns={"company_id"})})
 * @ORM\Entity(repositoryClass="\Office\Repository\ShopRepository")
 */
class Shop
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
     * @ORM\Column(name="title", type="string", length=126, precision=0, scale=0, nullable=false, unique=false)
     */
    private $title;

    /**
     * @var bool|null
     * @ORM\Column(name="is_secret", type="boolean", precision=0, scale=0, nullable=true, options={"default"="1"}, unique=false)
     */
    private $isSecret = '1';

    /**
     * @var string|null
     * @ORM\Column(name="kkt_params", type="string", length=255, precision=0, scale=0, nullable=true, unique=false)
     */
    private $kktParams;

    /**
     * @var string|null
     * @ORM\Column(name="url", type="string", length=512, precision=0, scale=0, nullable=true, unique=false)
     */
    private $url;

    /**
     * @var string|null
     * @ORM\Column(name="address", type="string", length=256, precision=0, scale=0, nullable=true, options={"comment"="Адресс точки приема платежей"}, unique=false)
     */
    private $address;

    /**
     * @var bool
     * @ORM\Column(name="is_test", type="boolean", precision=0, scale=0, nullable=false, unique=false)
     */
    private $isTest = false;

    /**
     * @var bool
     * @ORM\Column(name="is_single", type="boolean", precision=0, scale=0, nullable=false, unique=false)
     */
    private $isSingle = false;

    /**
     * @var bool
     * @ORM\Column(name="is_pack", type="boolean", precision=0, scale=0, nullable=false, unique=false)
     */
    private $isPack = false;

    /**
     * @var \Office\Entity\Company
     * @ORM\ManyToOne(targetEntity="Office\Entity\Company")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $company;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Office\Entity\Kkt", mappedBy="shop", cascade={"remove"})
     */
    private $kkt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->kkt = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Shop
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
     * Set isSecret.
     *
     * @param bool|null $isSecret
     *
     * @return Shop
     */
    public function setIsSecret($isSecret = null)
    {
        $this->isSecret = $isSecret;

        return $this;
    }

    /**
     * Get isSecret.
     *
     * @return bool|null
     */
    public function getIsSecret()
    {
        return $this->isSecret;
    }

    /**
     * Set kktParams.
     *
     * @param string|null $kktParams
     *
     * @return Shop
     */
    public function setKktParams($kktParams = null)
    {
        $this->kktParams = $kktParams;

        return $this;
    }

    /**
     * Get kktParams.
     *
     * @return string|null
     */
    public function getKktParams()
    {
        return $this->kktParams;
    }

    /**
     * Set url.
     *
     * @param string|null $url
     *
     * @return Shop
     */
    public function setUrl($url = null)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set address.
     *
     * @param string|null $address
     *
     * @return Shop
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
     * Set isTest.
     *
     * @param bool $isTest
     *
     * @return Shop
     */
    public function setIsTest($isTest)
    {
        $this->isTest = $isTest;

        return $this;
    }

    /**
     * Get isTest.
     *
     * @return bool
     */
    public function getIsTest()
    {
        return $this->isTest;
    }

    /**
     * Set isSingle.
     *
     * @param bool $isSingle
     *
     * @return Shop
     */
    public function setIsSingle($isSingle)
    {
        $this->isSingle = $isSingle;

        return $this;
    }

    /**
     * Get isSingle.
     *
     * @return bool
     */
    public function getIsSingle()
    {
        return $this->isSingle;
    }

    /**
     * Set isPack.
     *
     * @param bool $isPack
     *
     * @return Shop
     */
    public function setIsPack($isPack)
    {
        $this->isPack = $isPack;

        return $this;
    }

    /**
     * Get isPack.
     *
     * @return bool
     */
    public function getIsPack()
    {
        return $this->isPack;
    }

    /**
     * Set company.
     *
     * @param \Office\Entity\Company|null $company
     *
     * @return Shop
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
     * Add kkt.
     *
     * @param \Office\Entity\Kkt $kkt
     *
     * @return Shop
     */
    public function addKkt(\Office\Entity\Kkt $kkt)
    {
        $this->kkt[] = $kkt;

        return $this;
    }

    /**
     * Remove kkt.
     *
     * @param \Office\Entity\Kkt $kkt
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeKkt(\Office\Entity\Kkt $kkt)
    {
        return $this->kkt->removeElement($kkt);
    }

    /**
     * Get kkt.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getKkt()
    {
        return $this->kkt;
    }
}
