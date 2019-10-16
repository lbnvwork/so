<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 25.01.18
 * Time: 13:46
 */

namespace Permission\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Role
 * @ORM\Table(name="role", indexes={@ORM\Index(name="parent_id", columns={"parent_id"})})
 *
 * @ORM\Entity
 * @codeCoverageIgnore
 */
class Role
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="smallint", precision=0, scale=0, nullable=false, options={"unsigned"=true}, unique=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="role_name", type="string", length=32, precision=0, scale=0, nullable=false, unique=false)
     */
    private $roleName;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, precision=0, scale=0, nullable=false, unique=false)
     */
    private $title;

    /**
     * @var int|null
     *
     * @ORM\Column(name="parent_id", type="smallint", precision=0, scale=0, nullable=true, options={"unsigned"=true}, unique=false)
     */
    private $parentId;

    /**
     * @var \Permission\Entity\Role
     *
     * @ORM\ManyToOne(targetEntity="Permission\Entity\Role")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $parent;


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
     * Set roleName.
     *
     * @param string $roleName
     *
     * @return Role
     */
    public function setRoleName($roleName)
    {
        $this->roleName = $roleName;

        return $this;
    }

    /**
     * Get roleName.
     *
     * @return string
     */
    public function getRoleName()
    {
        return $this->roleName;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return Role
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
     * Set parentId.
     *
     * @param int|null $parentId
     *
     * @return Role
     */
    public function setParentId($parentId = null)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId.
     *
     * @return int|null
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set parent.
     *
     * @param \Permission\Entity\Role|null $parent
     *
     * @return Role
     */
    public function setParent(\Permission\Entity\Role $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return \Permission\Entity\Role|null
     */
    public function getParent()
    {
        return $this->parent;
    }
}
