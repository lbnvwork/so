<?php

namespace Office\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Processing
 * @ORM\Table(
 *     name="processing",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="external_id", columns={"external_id", "shop_id"})},
 *     indexes={@ORM\Index(name="IDX_886CAB2B4D16C4DD", columns={"shop_id"}), @ORM\Index(name="IDX_886CAB2B404E338F", columns={"kkt_id"})}
 *     )
 * @ORM\Entity(repositoryClass="\Office\Repository\ProcessingRepository")
 */
class Processing
{
    public const STATUS_ACCEPT = 1;
    public const STATUS_PREPARE = 2;
    public const STATUS_SEND_CLIENT = 3;
    public const STATUS_SUCCESS = 4;
    public const STATUS_PRINT_PROCESS = 5;
    public const STATUS_ERROR_PRINT = 6;
    public const STATUS_LIST = [
        self::STATUS_ACCEPT        => 'Принят',
        self::STATUS_PREPARE       => 'В обработке',
        self::STATUS_SEND_CLIENT   => 'Ожидает отправки на сайт клиента',
        self::STATUS_SUCCESS       => 'Проведен',
        self::STATUS_PRINT_PROCESS => 'В процессе печати',
        self::STATUS_ERROR_PRINT   => 'Ошибка печати, нужно исправить чек',
    ];
    public const OPERATION_SELL = 1;
    public const OPERATION_SELL_REFUND = 2;
    public const OPERATION_BUY = 3;
    public const OPERATION_BUY_REFUND = 4;
    public const OPERATION_SELL_CORRECTION = 5;
    public const OPERATION_BUY_CORRECTION = 6;
    public const OPERATION_LIST = [
        self::OPERATION_SELL            => 'Приход',
        self::OPERATION_SELL_REFUND     => 'Возврат прихода',
        self::OPERATION_BUY             => 'Расход',
        self::OPERATION_BUY_REFUND      => 'Возврат расхода',
        self::OPERATION_SELL_CORRECTION => 'Коррекция прихода',
        self::OPERATION_BUY_CORRECTION  => 'Коррекция расхода',
    ];

    /**
     * @var int
     * @ORM\Column(name="id", type="integer", precision=0, scale=0, nullable=false, options={"unsigned"=true}, unique=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(name="raw_data", type="text", length=65535, precision=0, scale=0, nullable=true, unique=false)
     */
    private $rawData;

    /**
     * @var \DateTime
     * @ORM\Column(name="datetime", type="datetime", precision=0, scale=0, nullable=false, options={"default"="CURRENT_TIMESTAMP"}, unique=false)
     */
    private $datetime = 'CURRENT_TIMESTAMP';

    /**
     * @var string|null
     * @ORM\Column(name="sum", type="decimal", precision=12, scale=2, nullable=true, unique=false)
     */
    private $sum;

    /**
     * @var string|null
     * @ORM\Column(name="callback_url", type="string", length=256, precision=0, scale=0, nullable=true, unique=false)
     */
    private $callbackUrl;

    /**
     * @var int
     * @ORM\Column(name="status", type="smallint", precision=0, scale=0, nullable=false, options={"unsigned"=true}, unique=false)
     */
    private $status;

    /**
     * @var string|null
     * @ORM\Column(name="external_id", type="string", length=256, precision=0, scale=0, nullable=true, unique=false)
     */
    private $externalId;

    /**
     * @var string|null
     * @ORM\Column(name="ofd_link", type="string", length=256, precision=0, scale=0, nullable=true, unique=false)
     */
    private $ofdLink;

    /**
     * @var int
     * @ORM\Column(name="operation", type="smallint", precision=0, scale=0, nullable=false, options={"unsigned"=true}, unique=false)
     */
    private $operation;

    /**
     * @var string|null
     * @ORM\Column(name="session_id", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $sessionId;

    /**
     * @var string|null
     * @ORM\Column(name="error", type="text", length=65535, precision=0, scale=0, nullable=true, unique=false)
     */
    private $error;

    /**
     * @var int|null
     * @ORM\Column(name="doc_number", type="integer", precision=0, scale=0, nullable=true, options={"unsigned"=true}, unique=false)
     */
    private $docNumber;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="date_print", type="datetime", precision=0, scale=0, nullable=true, unique=false)
     */
    private $datePrint;

    /**
     * @var int
     *
     * @ORM\Column(name="shift_number", type="integer", precision=0, scale=0, nullable=true, options={"unsigned"=true}, unique=false)
     */
    private $shiftNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="fn_number", type="string", length=16, precision=0, scale=0, nullable=true, unique=false)
     */
    private $fnNumber;

    /**
     * @var int|null
     *
     * @ORM\Column(name="receipt_number", type="integer", precision=0, scale=0, nullable=true, options={"unsigned"=true}, unique=false)
     */
    private $receiptNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ecr_registration_number", type="string", length=16, precision=0, scale=0, nullable=true, unique=false)
     */
    private $ecrRegistrationNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="document_attribute", type="string", length=16, precision=0, scale=0, nullable=true, unique=false)
     */
    private $documentAttribute;

    /**
     * @var \Office\Entity\Kkt
     * @ORM\ManyToOne(targetEntity="Office\Entity\Kkt")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="kkt_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $kkt;

    /**
     * @var \Office\Entity\Shop
     * @ORM\ManyToOne(targetEntity="Office\Entity\Shop")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="shop_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $shop;


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
     * Set rawData.
     *
     * @param string|null $rawData
     *
     * @return Processing
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
     * Set datetime.
     *
     * @param \DateTime $datetime
     *
     * @return Processing
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
     * Set sum.
     *
     * @param string|null $sum
     *
     * @return Processing
     */
    public function setSum($sum = null)
    {
        $this->sum = $sum;

        return $this;
    }

    /**
     * Get sum.
     *
     * @return string|null
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * Set callbackUrl.
     *
     * @param string|null $callbackUrl
     *
     * @return Processing
     */
    public function setCallbackUrl($callbackUrl = null)
    {
        $this->callbackUrl = $callbackUrl;

        return $this;
    }

    /**
     * Get callbackUrl.
     *
     * @return string|null
     */
    public function getCallbackUrl()
    {
        return $this->callbackUrl;
    }

    /**
     * Set status.
     *
     * @param int $status
     *
     * @return Processing
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
     * Set externalId.
     *
     * @param string|null $externalId
     *
     * @return Processing
     */
    public function setExternalId($externalId = null)
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * Get externalId.
     *
     * @return string|null
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * Set ofdLink.
     *
     * @param string|null $ofdLink
     *
     * @return Processing
     */
    public function setOfdLink($ofdLink = null)
    {
        $this->ofdLink = $ofdLink;

        return $this;
    }

    /**
     * Get ofdLink.
     *
     * @return string|null
     */
    public function getOfdLink()
    {
        return $this->ofdLink;
    }

    /**
     * Set operation.
     *
     * @param int $operation
     *
     * @return Processing
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;

        return $this;
    }

    /**
     * Get operation.
     *
     * @return int
     */
    public function getOperation(): ?int
    {
        return $this->operation;
    }

    /**
     * Set sessionId.
     *
     * @param string|null $sessionId
     *
     * @return Processing
     */
    public function setSessionId($sessionId = null)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * Get sessionId.
     *
     * @return string|null
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Set error.
     *
     * @param string|null $error
     *
     * @return Processing
     */
    public function setError($error = null)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get error.
     *
     * @return string|null
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set docNumber.
     *
     * @param int|null $docNumber
     *
     * @return Processing
     */
    public function setDocNumber($docNumber = null)
    {
        $this->docNumber = $docNumber;

        return $this;
    }

    /**
     * Get docNumber.
     *
     * @return int|null
     */
    public function getDocNumber(): ?int
    {
        return $this->docNumber;
    }

    /**
     * Set datePrint.
     *
     * @param \DateTime|null $datePrint
     *
     * @return Processing
     */
    public function setDatePrint($datePrint = null)
    {
        $this->datePrint = $datePrint;

        return $this;
    }

    /**
     * Get datePrint.
     *
     * @return \DateTime|null
     */
    public function getDatePrint()
    {
        return $this->datePrint;
    }

    /**
     * Set shiftNumber.
     *
     * @param int $shiftNumber
     *
     * @return Processing
     */
    public function setShiftNumber($shiftNumber)
    {
        $this->shiftNumber = $shiftNumber;

        return $this;
    }

    /**
     * Get shiftNumber.
     *
     * @return int
     */
    public function getShiftNumber()
    {
        return $this->shiftNumber;
    }

    /**
     * Set fnNumber.
     *
     * @param string|null $fnNumber
     *
     * @return Processing
     */
    public function setFnNumber($fnNumber = null)
    {
        $this->fnNumber = $fnNumber;

        return $this;
    }

    /**
     * Get fnNumber.
     *
     * @return string|null
     */
    public function getFnNumber()
    {
        return $this->fnNumber;
    }

    /**
     * Set receiptNumber.
     *
     * @param int|null $receiptNumber
     *
     * @return Processing
     */
    public function setReceiptNumber($receiptNumber = null)
    {
        $this->receiptNumber = $receiptNumber;

        return $this;
    }

    /**
     * Get receiptNumber.
     *
     * @return int|null
     */
    public function getReceiptNumber()
    {
        return $this->receiptNumber;
    }

    /**
     * Set ecrRegistrationNumber.
     *
     * @param string|null $ecrRegistrationNumber
     *
     * @return Processing
     */
    public function setEcrRegistrationNumber($ecrRegistrationNumber = null)
    {
        $this->ecrRegistrationNumber = $ecrRegistrationNumber;

        return $this;
    }

    /**
     * Get ecrRegistrationNumber.
     *
     * @return string|null
     */
    public function getEcrRegistrationNumber()
    {
        return $this->ecrRegistrationNumber;
    }

    /**
     * Set documentAttribute.
     *
     * @param string|null $documentAttribute
     *
     * @return Processing
     */
    public function setDocumentAttribute($documentAttribute = null)
    {
        $this->documentAttribute = $documentAttribute;

        return $this;
    }

    /**
     * Get documentAttribute.
     *
     * @return string|null
     */
    public function getDocumentAttribute()
    {
        return $this->documentAttribute;
    }

    /**
     * Set kkt.
     *
     * @param \Office\Entity\Kkt|null $kkt
     *
     * @return Processing
     */
    public function setKkt(\Office\Entity\Kkt $kkt = null)
    {
        $this->kkt = $kkt;

        return $this;
    }

    /**
     * Get kkt.
     *
     * @return \Office\Entity\Kkt|null
     */
    public function getKkt()
    {
        return $this->kkt;
    }

    /**
     * Set shop.
     *
     * @param \Office\Entity\Shop|null $shop
     *
     * @return Processing
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
     * @return string
     */
    public function getHumanStatus(): string
    {
        return self::STATUS_LIST[$this->getStatus()] ?? 'Ошибка';
    }

    /**
     * @return string
     */
    public function getHumanOperation(): string
    {
        return self::OPERATION_LIST[$this->getOperation()] ?? 'Ошибка';
    }
}
