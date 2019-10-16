<?php

namespace Auth\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserHasRole
 *
 * @ORM\Table(name="user_has_role", indexes={@ORM\Index(name="IDX_EAB8B535A76ED395", columns={"user_id"})})
 * @ORM\Entity
 */
class UserHasRole
{
    /**
     * @var string
     *
     * @ORM\Column(name="role_name", type="string", length=32, precision=0, scale=0, nullable=false, unique=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $roleName;

    /**
     * @var \Auth\Entity\User
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Auth\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;


    /**
     * Set roleName.
     *
     * @param string $roleName
     *
     * @return UserHasRole
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
     * Set user.
     *
     * @param \Auth\Entity\User $user
     *
     * @return UserHasRole
     */
    public function setUser(\Auth\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \Auth\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
