<?php

namespace Office\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Fn
 * @ORM\Table(name="fn")
 *
 * @ORM\Entity
 */
class Fn
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer", precision=0, scale=0, nullable=false, options={"unsigned"=true}, unique=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(name="company", type="string", length=126, precision=0, scale=0, nullable=true, unique=false)
     */
    private $company;

    /**
     * @var string|null
     * @ORM\Column(name="serial_number", type="string", length=132, precision=0, scale=0, nullable=true, unique=false)
     */
    private $serialNumber;

    /**
     * @var string|null
     * @ORM\Column(name="fn_number", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $fnNumber;

    /**
     * @var string|null
     * @ORM\Column(name="fn_version", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $fnVersion;

    /**
     * @var int|null
     * @ORM\Column(name="status", type="smallint", precision=0, scale=0, nullable=true, unique=false)
     */
    private $status;

    /**
     * @var bool|null
     * @ORM\Column(name="is_fiscalized", type="boolean", precision=0, scale=0, nullable=true, unique=false)
     */
    private $isFiscalized;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="date_fiscalized", type="datetime", precision=0, scale=0, nullable=true, unique=false)
     */
    private $dateFiscalized;

    /**
     * @var bool|null
     * @ORM\Column(name="is_deleted", type="boolean", precision=0, scale=0, nullable=true, unique=false)
     */
    private $isDeleted;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="date_deleted", type="datetime", precision=0, scale=0, nullable=true, unique=false)
     */
    private $dateDeleted;

    /**
     * @var string|null
     * @ORM\Column(name="document_number", type="string", length=64, precision=0, scale=0, nullable=true, unique=false)
     */
    private $documentNumber;


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
     * Set company.
     *
     * @param string|null $company
     *
     * @return Fn
     */
    public function setCompany($company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company.
     *
     * @return string|null
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set serialNumber.
     *
     * @param string|null $serialNumber
     *
     * @return Fn
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
     * Set fnNumber.
     *
     * @param string|null $fnNumber
     *
     * @return Fn
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
     * Set fnVersion.
     *
     * @param string|null $fnVersion
     *
     * @return Fn
     */
    public function setFnVersion($fnVersion = null)
    {
        $this->fnVersion = $fnVersion;

        return $this;
    }

    /**
     * Get fnVersion.
     *
     * @return string|null
     */
    public function getFnVersion()
    {
        return $this->fnVersion;
    }

    /**
     * Set status.
     *
     * @param int|null $status
     *
     * @return Fn
     */
    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return int|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set isFiscalized.
     *
     * @param bool|null $isFiscalized
     *
     * @return Fn
     */
    public function setIsFiscalized($isFiscalized = null)
    {
        $this->isFiscalized = $isFiscalized;

        return $this;
    }

    /**
     * Get isFiscalized.
     *
     * @return bool|null
     */
    public function getIsFiscalized()
    {
        return $this->isFiscalized;
    }

    /**
     * Set dateFiscalized.
     *
     * @param \DateTime|null $dateFiscalized
     *
     * @return Fn
     */
    public function setDateFiscalized($dateFiscalized = null)
    {
        $this->dateFiscalized = $dateFiscalized;

        return $this;
    }

    /**
     * Get dateFiscalized.
     *
     * @return \DateTime|null
     */
    public function getDateFiscalized()
    {
        return $this->dateFiscalized;
    }

    /**
     * Set isDeleted.
     *
     * @param bool|null $isDeleted
     *
     * @return Fn
     */
    public function setIsDeleted($isDeleted = null)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted.
     *
     * @return bool|null
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
     * @return Fn
     */
    public function setDateDeleted($dateDeleted = null)
    {
        $this->dateDeleted = $dateDeleted;

        return $this;
    }

    /**
     * Get dateDeleted.
     *
     * @return \DateTime|null
     */
    public function getDateDeleted()
    {
        return $this->dateDeleted;
    }

    /**
     * Set documentNumber.
     *
     * @param string|null $documentNumber
     *
     * @return Fn
     */
    public function setDocumentNumber($documentNumber = null)
    {
        $this->documentNumber = $documentNumber;

        return $this;
    }

    /**
     * Get documentNumber.
     *
     * @return string|null
     */
    public function getDocumentNumber()
    {
        return $this->documentNumber;
    }
}
