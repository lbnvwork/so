<?php

namespace ApiInsales\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InsalesShop
 * @ORM\Table(
 *     name="insales_shop",
 *     indexes={
 *     @ORM\Index(name="shop_schetmash", columns={"shop_schetmash"}),
 *     @ORM\Index(name="user_schetmash", columns={"user_schetmash"})
 *     })
 *
 * @ORM\Entity
 */
class InsalesShop
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer", precision=0, scale=0, nullable=false, unique=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=32, precision=0, scale=0, nullable=false, options={"fixed"=true}, unique=false)
     */
    private $password;

    /**
     * @var string
     * @ORM\Column(name="shop_insales", type="string", length=100, precision=0, scale=0, nullable=false, unique=false)
     */
    private $shopInsales;

    /**
     * @var int
     * @ORM\Column(name="insales_id", type="integer", precision=0, scale=0, nullable=false, unique=false)
     */
    private $insalesId;

    /**
     * @var int
     * @ORM\Column(name="user_id", type="integer", precision=0, scale=0, nullable=true, unique=false)
     */
    private $userId;

    /**
     * @var int|null
     * @ORM\Column(name="hook_id", type="integer", nullable=true)
     */
    private $hookId;

    /**
     * @var \Office\Entity\Shop
     * @ORM\ManyToOne(targetEntity="Office\Entity\Shop")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="shop_schetmash", referencedColumnName="id", nullable=true)
     * })
     */
    private $shopSchetmash;

    /**
     * @var \Auth\Entity\User
     * @ORM\ManyToOne(targetEntity="Auth\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_schetmash", referencedColumnName="id", nullable=true)
     * })
     */
    private $userSchetmash;

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
     * Set password.
     *
     * @param string $password
     *
     * @return InsalesShop
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set shopInsales.
     *
     * @param string $shopInsales
     *
     * @return InsalesShop
     */
    public function setShopInsales($shopInsales)
    {
        $this->shopInsales = $shopInsales;

        return $this;
    }

    /**
     * Get shopInsales.
     *
     * @return string
     */
    public function getShopInsales()
    {
        return $this->shopInsales;
    }

    /**
     * Set insalesId.
     *
     * @param int $insalesId
     *
     * @return InsalesShop
     */
    public function setInsalesId($insalesId)
    {
        $this->insalesId = $insalesId;

        return $this;
    }

    /**
     * Get insalesId.
     *
     * @return int
     */
    public function getInsalesId()
    {
        return $this->insalesId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return InsalesShop
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set shopSchetmash.
     *
     * @param \Office\Entity\Shop|null $shopSchetmash
     *
     * @return InsalesShop
     */
    public function setShopSchetmash(\Office\Entity\Shop $shopSchetmash = null)
    {
        $this->shopSchetmash = $shopSchetmash;

        return $this;
    }

    /**
     * Get shopSchetmash.
     *
     * @return \Office\Entity\Shop|null
     */
    public function getShopSchetmash()
    {
        return $this->shopSchetmash;
    }

    /**
     * Set userSchetmash.
     *
     * @param \Auth\Entity\User $userSchetmash
     *
     * @return InsalesShop|null
     */
    public function setUserSchetmash(\Auth\Entity\User $userSchetmash)
    {
        $this->userSchetmash = $userSchetmash;

        return $this;
    }

    /**
     * Get userSchetmash.
     *
     * @return \Auth\Entity\User|null
     */
    public function getUserSchetmash()
    {
        return $this->userSchetmash;
    }

    /**
     * Set hookId.
     *
     * @param int|null $hookId
     *
     * @return InsalesShop
     */
    public function setHookId($hookId = null)
    {
        $this->hookId = $hookId;

        return $this;
    }

    /**
     * Get hookId.
     *
     * @return int|null
     */
    public function getHookId()
    {
        return $this->hookId;
    }
}
