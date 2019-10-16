<?php

namespace Office\Entity;

use Auth\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * ApiKey
 *
 * @ORM\Table(name="api_key", indexes={@ORM\Index(name="user_id", columns={"user_id"})})
 * @ORM\Entity
 */
class ApiKey
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
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=32, precision=0, scale=0, nullable=false, unique=false)
     */
    private $login;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=32, precision=0, scale=0, nullable=false, unique=false)
     */
    private $password;

    /**
     * @var string|null
     *
     * @ORM\Column(name="token", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $token;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_create", type="datetime", precision=0, scale=0, nullable=false, options={"default"="CURRENT_TIMESTAMP"}, unique=false)
     */
    private $dateCreate = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date_expired_token", type="datetime", precision=0, scale=0, nullable=true, unique=false)
     */
    private $dateExpiredToken;

    /**
     * @var \Auth\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Auth\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $user;

    /**
     * ApiKey constructor.
     * @param User|null $user
     */
    public function __construct(User $user = null)
    {
        $this->setUser($user);
        $this->setDateCreate(new \DateTime());
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
     * Set login.
     *
     * @param string $login
     *
     * @return ApiKey
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login.
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return ApiKey
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
     * Set token.
     *
     * @param string|null $token
     *
     * @return ApiKey
     */
    public function setToken($token = null)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token.
     *
     * @return string|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set dateCreate.
     *
     * @param \DateTime $dateCreate
     *
     * @return ApiKey
     */
    public function setDateCreate($dateCreate)
    {
        $this->dateCreate = $dateCreate;

        return $this;
    }

    /**
     * Get dateCreate.
     *
     * @return \DateTime
     */
    public function getDateCreate()
    {
        return $this->dateCreate;
    }

    /**
     * Set dateExpiredToken.
     *
     * @param \DateTime|null $dateExpiredToken
     *
     * @return ApiKey
     */
    public function setDateExpiredToken($dateExpiredToken = null)
    {
        $this->dateExpiredToken = $dateExpiredToken;

        return $this;
    }

    /**
     * Get dateExpiredToken.
     *
     * @return \DateTime|null
     */
    public function getDateExpiredToken()
    {
        return $this->dateExpiredToken;
    }

    /**
     * Set user.
     *
     * @param \Auth\Entity\User|null $user
     *
     * @return ApiKey
     */
    public function setUser(\Auth\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \Auth\Entity\User|null
     */
    public function getUser()
    {
        return $this->user;
    }
}
