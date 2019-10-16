<?php

namespace Office\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Kkt
 * @ORM\Table(
 *     name="kkt",
 *     indexes={
 *     @ORM\Index(name="shop_id", columns={"shop_id"}),
 *     @ORM\Index(name="tariff_id", columns={"tariff_id"}),
 *     @ORM\Index(name="tariff_next_id", columns={"tariff_next_id"})
 *      }
 *     )
 * @ORM\Entity(repositoryClass="\Office\Repository\ShopRepository")
 */
class Kkt implements \JsonSerializable
{
    public const FN_DEBUG_VERSION = 'fn debug v 2.13';
    public const TEST_KKT_ID = '17000675';
    public const KEAZ_KKT_ID = '17000622';

    /**
     * @var int
     * @ORM\Column(name="id", type="integer", precision=0, scale=0, nullable=false, options={"unsigned"=true}, unique=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="date_expired", type="date", precision=0, scale=0, nullable=true, unique=false)
     */
    private $dateExpired;

    /**
     * @var bool
     * @ORM\Column(name="is_enabled", type="boolean", precision=0, scale=0, nullable=false, unique=false)
     */
    private $isEnabled = false;

    /**
     * @var bool
     * @ORM\Column(name="is_fiscalized", type="boolean", precision=0, scale=0, nullable=false, unique=false)
     */
    private $isFiscalized = false;

    /**
     * @var bool
     * @ORM\Column(name="is_deleted", type="boolean", precision=0, scale=0, nullable=false, unique=false)
     */
    private $isDeleted = false;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="date_deleted", type="datetime", precision=0, scale=0, nullable=true, unique=false)
     */
    private $dateDeleted;

    /**
     * @var bool
     * @ORM\Column(name="is_send_fn", type="boolean", precision=0, scale=0, nullable=false, unique=false)
     */
    private $isSendFn = false;

    /**
     * @var string|null
     * @ORM\Column(name="serial_number", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $serialNumber;

    /**
     * @var string|null
     * @ORM\Column(name="fs_number", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $fsNumber;

    /**
     * @var string|null
     * @ORM\Column(name="fs_version", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $fsVersion;

    /**
     * @var int|null
     * @ORM\Column(name="fn_live_time", type="integer", precision=0, scale=0, nullable=true, options={"unsigned"=true}, unique=false)
     */
    private $fnLiveTime;

    /**
     * @var string|null
     * @ORM\Column(name="reg_number", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $regNumber;

    /**
     * @var string|null
     * @ORM\Column(name="inn", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $inn;

    /**
     * @var string|null
     * @ORM\Column(name="payment_address", type="string", length=126, precision=0, scale=0, nullable=true, unique=false)
     */
    private $paymentAddress;

    /**
     * @var string|null
     * @ORM\Column(name="raw_data", type="text", length=65535, precision=0, scale=0, nullable=true, unique=false)
     */
    private $rawData;

    /**
     * @var string|null
     * @ORM\Column(name="fiscal_raw_data", type="text", length=65535, precision=0, scale=0, nullable=true, unique=false)
     */
    private $fiscalRawData;

    /**
     * @var string|null
     * @ORM\Column(name="fiscal_command", type="string", length=64, precision=0, scale=0, nullable=true, unique=false)
     */
    private $fiscalCommand;

    /**
     * @var string|null
     * @ORM\Column(name="close_fn_raw_data", type="text", length=65535, precision=0, scale=0, nullable=true, unique=false)
     */
    private $closeFnRawData;

    /**
     * @var string|null
     * @ORM\Column(name="rnm_file", type="string", length=512, precision=0, scale=0, nullable=true, unique=false)
     */
    private $rnmFile;

    /**
     * @var string|null
     * @ORM\Column(name="fiscal_result_file", type="string", length=64, precision=0, scale=0, nullable=true, unique=false)
     */
    private $fiscalResultFile;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="tariff_date_start", type="date", precision=0, scale=0, nullable=true, options={"comment"="Дата начала действия тарифа"}, unique=false)
     */
    private $tariffDateStart;

    /**
     * @var \Office\Entity\Shop
     * @ORM\ManyToOne(targetEntity="Office\Entity\Shop")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="shop_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $shop;

    /**
     * @var \Office\Entity\Tariff
     * @ORM\ManyToOne(targetEntity="Office\Entity\Tariff")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tariff_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $tariff;

    /**
     * @var \Office\Entity\Tariff
     * @ORM\ManyToOne(targetEntity="Office\Entity\Tariff")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tariff_next_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $tariffNext;

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
     * Set dateExpired.
     *
     * @param \DateTime|null $dateExpired
     *
     * @return Kkt
     */
    public function setDateExpired($dateExpired = null)
    {
        $this->dateExpired = $dateExpired;

        return $this;
    }

    /**
     * Get dateExpired.
     *
     * @return \DateTime|null
     */
    public function getDateExpired()
    {
        return $this->dateExpired;
    }

    /**
     * Set isEnabled.
     *
     * @param bool $isEnabled
     *
     * @return Kkt
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
    public function getIsEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * Set isFiscalized.
     *
     * @param bool $isFiscalized
     *
     * @return Kkt
     */
    public function setIsFiscalized($isFiscalized)
    {
        $this->isFiscalized = $isFiscalized;

        return $this;
    }

    /**
     * Get isFiscalized.
     *
     * @return bool
     */
    public function getIsFiscalized()
    {
        return $this->isFiscalized;
    }

    /**
     * Set isDeleted.
     *
     * @param bool $isDeleted
     *
     * @return Kkt
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
     * Set dateDeleted.
     *
     * @param \DateTime|null $dateDeleted
     *
     * @return Kkt
     */
    public function setDateDeleted($_dateDeleted)
    {
        $this->dateDeleted = $_dateDeleted;

        return $this;
    }

    /**
     * Get dateDeleted.
     *
     * @return \DateTime|null
     */
    public function getDateDeleted(): ?\DateTime
    {
        return $this->dateDeleted;
    }

    /**
     * Set isSendFn.
     *
     * @param bool $isSendFn
     *
     * @return Kkt
     */
    public function setIsSendFn($isSendFn)
    {
        $this->isSendFn = $isSendFn;

        return $this;
    }

    /**
     * Get isSendFn.
     *
     * @return bool
     */
    public function getIsSendFn()
    {
        return $this->isSendFn;
    }

    /**
     * Set serialNumber.
     *
     * @param string|null $serialNumber
     *
     * @return Kkt
     */
    public function setSerialNumber($serialNumber = null)
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    /**
     * Get serialNumber.
     *
     * @return string|null
     */
    public function getSerialNumber()
    {
        return $this->serialNumber;
    }

    /**
     * Set fsNumber.
     *
     * @param string|null $fsNumber
     *
     * @return Kkt
     */
    public function setFsNumber($fsNumber = null)
    {
        $this->fsNumber = $fsNumber;

        return $this;
    }

    /**
     * Get fsNumber.
     *
     * @return string|null
     */
    public function getFsNumber()
    {
        return $this->fsNumber;
    }

    /**
     * Set fsVersion.
     *
     * @param string|null $fsVersion
     *
     * @return Kkt
     */
    public function setFsVersion($fsVersion = null)
    {
        $this->fsVersion = $fsVersion;

        return $this;
    }

    /**
     * Get fsVersion.
     *
     * @return string|null
     */
    public function getFsVersion()
    {
        return $this->fsVersion;
    }

    /**
     * Set fnLiveTime.
     *
     * @param int|null $fnLiveTime
     *
     * @return Kkt
     */
    public function setFnLiveTime($fnLiveTime = null)
    {
        $this->fnLiveTime = $fnLiveTime;

        return $this;
    }

    /**
     * Get fnLiveTime.
     *
     * @return int|null
     */
    public function getFnLiveTime()
    {
        return $this->fnLiveTime;
    }

    /**
     * Set regNumber.
     *
     * @param string|null $regNumber
     *
     * @return Kkt
     */
    public function setRegNumber($regNumber = null)
    {
        $this->regNumber = $regNumber;

        return $this;
    }

    /**
     * Get regNumber.
     *
     * @return string|null
     */
    public function getRegNumber()
    {
        return $this->regNumber;
    }

    /**
     * Set inn.
     *
     * @param string|null $inn
     *
     * @return Kkt
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
     * Set paymentAddress.
     *
     * @param string|null $paymentAddress
     *
     * @return Kkt
     */
    public function setPaymentAddress($paymentAddress = null)
    {
        $this->paymentAddress = $paymentAddress;

        return $this;
    }

    /**
     * Get inn.
     *
     * @return string|null
     */
    public function getPaymentAddress(): ?string
    {
        return $this->paymentAddress;
    }

    /**
     * Set rawData.
     *
     * @param string|null $rawData
     *
     * @return Kkt
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
     * @return null|string
     */
    public function getFiscalRawData(): ?string
    {
        return $this->fiscalRawData;
    }

    /**
     * @param null|string $_fiscalRawData
     *
     * @return $this;
     */
    public function setFiscalRawData($_fiscalRawData)
    {
        $this->fiscalRawData = $_fiscalRawData;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getFiscalCommand(): ?string
    {
        return $this->fiscalCommand;
    }

    /**
     * @param null|string $_ficalCommand
     *
     * @return $this;
     */
    public function setFiscalCommand($_ficalCommand)
    {
        $this->fiscalCommand = $_ficalCommand;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCloseFnRawData(): ?string
    {
        return $this->closeFnRawData;
    }

    /**
     * @param null|string $_closeFnRawData
     *
     * @return $this;
     */
    public function setCloseFnRawData($_closeFnRawData)
    {
        $this->closeFnRawData = $_closeFnRawData;

        return $this;
    }

    /**
     * @param null|string $_rnmFile
     *
     * @return $this;
     */
    public function setRnmFile($_rnmFile)
    {
        $this->rnmFile = $_rnmFile;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRnmFile(): ?string
    {
        return $this->rnmFile;
    }

    /**
     * Set fiscalResultFile.
     *
     * @param null|string $_fiscalResultFile
     *
     * @return $this;
     */
    public function setFiscalResultFile($_fiscalResultFile)
    {
        $this->fiscalResultFile = $_fiscalResultFile;

        return $this;
    }

    /**
     * Get fiscalResultFile.
     *
     * @return null|string
     */
    public function getFiscalResultFile(): ?string
    {
        return $this->fiscalResultFile;
    }

    /**
     * Set tariffDateStart.
     *
     * @param \DateTime|null $tariffDateStart
     *
     * @return Kkt
     */
    public function setTariffDateStart($tariffDateStart = null)
    {
        $this->tariffDateStart = $tariffDateStart;

        return $this;
    }

    /**
     * Get tariffDateStart.
     *
     * @return \DateTime|null
     */
    public function getTariffDateStart()
    {
        return $this->tariffDateStart;
    }

    /**
     * Set shop.
     *
     * @param \Office\Entity\Shop|null $shop
     *
     * @return Kkt
     */
    public function setShop(\Office\Entity\Shop $shop = null)
    {
        $this->shop = $shop;

        return $this;
    }

    /**
     * Get shop.
     *
     * @return \Office\Entity\Shop|null
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * Set tariff.
     *
     * @param \Office\Entity\Tariff|null $tariff
     *
     * @return Kkt
     */
    public function setTariff(\Office\Entity\Tariff $tariff = null)
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * Get tariff.
     *
     * @return \Office\Entity\Tariff|null
     */
    public function getTariff()
    {
        return $this->tariff;
    }

    /**
     * Set tariffNext.
     *
     * @param \Office\Entity\Tariff|null $tariffNext
     *
     * @return Kkt
     */
    public function setTariffNext(\Office\Entity\Tariff $tariffNext = null)
    {
        $this->tariffNext = $tariffNext;

        return $this;
    }

    /**
     * Get tariffNext.
     *
     * @return \Office\Entity\Tariff|null
     */
    public function getTariffNext()
    {
        return $this->tariffNext;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'inn'          => $this->getInn(),
            'serilaNumber' => $this->getSerialNumber(),
            'fsVersion'    => $this->getFsVersion(),
            'fsNumber'     => $this->getFsNumber(),
        ];
    }
}
