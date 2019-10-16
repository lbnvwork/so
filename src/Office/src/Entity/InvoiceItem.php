<?php

namespace Office\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InvoiceItem
 *
 * @ORM\Table(
 *     name="invoice_item",
 *     indexes={
 *     @ORM\Index(name="invoice_id", columns={"invoice_id"}),
 *     @ORM\Index(name="IDX_1DDE477BED5CA9E6", columns={"service_id"}),
 *     @ORM\Index(name="ix_tariff_id", columns={"tariff_id"})
 *      }
 *     )
 * @ORM\Entity
 */
class InvoiceItem
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
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", length=255, precision=0, scale=0, nullable=true, unique=false)
     */
    private $title;

    /**
     * @var int
     *
     * @ORM\Column(name="price", type="integer", precision=0, scale=0, nullable=false, unique=false)
     */
    private $price;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer", precision=0, scale=0, nullable=false, unique=false)
     */
    private $quantity;

    /**
     * @var int
     *
     * @ORM\Column(name="sum", type="integer", precision=0, scale=0, nullable=false, unique=false)
     */
    private $sum;

    /**
     * @var \Office\Entity\Invoice
     *
     * @ORM\ManyToOne(targetEntity="Office\Entity\Invoice")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="invoice_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $invoice;

    /**
     * @var \App\Entity\Service
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Service")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="service_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $service;

    /**
     * @var \Office\Entity\Tariff
     *
     * @ORM\ManyToOne(targetEntity="Office\Entity\Tariff")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tariff_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $tariff;


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
     * @param string|null $title
     *
     * @return InvoiceItem
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
     * Set price.
     *
     * @param int $price
     *
     * @return InvoiceItem
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price.
     *
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set quantity.
     *
     * @param int $quantity
     *
     * @return InvoiceItem
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity.
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set sum.
     *
     * @param int $sum
     *
     * @return InvoiceItem
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
     * Set invoice.
     *
     * @param \Office\Entity\Invoice|null $invoice
     *
     * @return InvoiceItem
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
     * Set service.
     *
     * @param \App\Entity\Service|null $service
     *
     * @return InvoiceItem
     */
    public function setService(\App\Entity\Service $service = null)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service.
     *
     * @return \App\Entity\Service|null
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set tariff.
     *
     * @param \Office\Entity\Tariff|null $tariff
     *
     * @return InvoiceItem
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
}
