<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * File
 * @ORM\Table(name="file", indexes={@ORM\Index(name="category_id", columns={"category_id"})})
 *
 * @ORM\Entity
 */
class File
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
     * @ORM\Column(name="title", type="string", length=1024, precision=0, scale=0, nullable=true, unique=false)
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(name="filename", type="string", length=1024, precision=0, scale=0, nullable=false, unique=false)
     */
    private $filename;

    /**
     * @var int
     * @ORM\Column(name="order", type="integer", precision=0, scale=0, nullable=false, options={"unsigned"=true}, unique=false)
     */
    private $order;

    /**
     * @var \App\Entity\FileCategory
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\FileCategory")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $category;

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
     * @return File
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
     * Set filename.
     *
     * @param string $filename
     *
     * @return File
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set order.
     *
     * @param int $order
     *
     * @return File
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order.
     *
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set category.
     *
     * @param \App\Entity\FileCategory|null $category
     *
     * @return File
     */
    public function setCategory(\App\Entity\FileCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category.
     *
     * @return \App\Entity\FileCategory
     */
    public function getCategory()
    {
        return $this->category;
    }
}
