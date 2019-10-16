<?php

namespace Auth\Entity;

use App\Service\DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Permission\Entity\Role;
use Zend\Expressive\Authentication\UserInterface;

/**
 * User
 * @ORM\Table(name="user",
 *     indexes={@ORM\Index(name="IDX_8D93D6493CCAA4B7", columns={"referral_id"}), @ORM\Index(name="IDX_8D93D64992348FD2", columns={"tariff_id"})}
 *     )
 *
 * @ORM\Entity
 */
class User implements UserInterface
{
    /** @var int Id тестового пользователя на которого действуют ограничения */
    public const TEST_USER_ID = 2;

    /**
     * @var int
     * @ORM\Column(name="id", type="integer", precision=0, scale=0, nullable=false, unique=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=32, precision=0, scale=0, nullable=false, unique=false)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=60, precision=0, scale=0, nullable=true, unique=false)
     */
    private $password;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $firstName;

    /**
     * @var string
     * @ORM\Column(name="middle_name", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $middleName;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $lastName;

    /**
     * @var string|null
     * @ORM\Column(name="hash_key", type="string", length=32, precision=0, scale=0, nullable=true, unique=false)
     */
    private $hashKey;

    /**
     * @var string|null
     * @ORM\Column(name="phone", type="string", length=34, precision=0, scale=0, nullable=true, unique=false)
     */
    private $phone;

    /**
     * @var bool
     * @ORM\Column(name="is_confirmed", type="boolean", precision=0, scale=0, nullable=false, unique=false)
     */
    private $isConfirmed = 0;

    /**
     * @var int
     * @ORM\Column(name="robo_promo", type="smallint", precision=0, scale=0, nullable=false, unique=false)
     */
    private $roboPromo = 0;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_create", type="datetime", precision=0, scale=0, nullable=false, options={"default"="CURRENT_TIMESTAMP"}, unique=false)
     */
    private $dateCreate = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime
     * @ORM\Column(name="date_last_auth", type="datetime", precision=0, scale=0, nullable=true, unique=false)
     */
    private $dateLastAuth;

    /**
     * @var bool
     * @ORM\Column(name="is_beginner", type="boolean", precision=0, scale=0, nullable=false, unique=false)
     */
    private $isBeginner;

    /**
     * @var \Auth\Entity\User
     * @ORM\ManyToOne(targetEntity="Auth\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="referral_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $referral;

    /**
     * @var \Office\Entity\Tariff
     * @ORM\ManyToOne(targetEntity="Office\Entity\Tariff")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tariff_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $tariff;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\ManyToMany(targetEntity="Office\Entity\Company", inversedBy="user")
     * @ORM\JoinTable(name="user_has_company",
     *   joinColumns={
     *     @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=true)
     *   }
     * )
     */
    private $company;

    /**
     * @var UserHasRole
     * @ORM\OneToMany(targetEntity="Auth\Entity\UserHasRole", mappedBy="user", cascade={"ALL"}, indexBy="roleName")
     */
    private $userRole;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->company = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userRole = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setDateCreate(new DateTime());
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
     * Set Id.
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword(string $password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Set firstName.
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName.
     *
     * @return string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * Set middleName.
     *
     * @param string $middleName
     *
     * @return User
     */
    public function setMiddleName(string $middleName)
    {
        $this->middleName = $middleName;

        return $this;
    }

    /**
     * Get middleName.
     *
     * @return string
     */
    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    /**
     * Set lastName.
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName.
     *
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * Set hashKey.
     *
     * @param string|null $hashKey
     *
     * @return User
     */
    public function setHashKey(?string $hashKey)
    {
        $this->hashKey = $hashKey;

        return $this;
    }

    /**
     * Get hashKey.
     *
     * @return string|null
     */
    public function getHashKey(): ?string
    {
        return $this->hashKey;
    }

    /**
     * Set phone.
     *
     * @param string|null $phone
     *
     * @return User
     */
    public function setPhone($phone = null)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone.
     *
     * @return string|null
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set isConfirmed.
     *
     * @param bool $isConfirmed
     *
     * @return User
     */
    public function setIsConfirmed($isConfirmed)
    {
        $this->isConfirmed = $isConfirmed;

        return $this;
    }

    /**
     * Get isConfirmed.
     *
     * @return bool
     */
    public function getIsConfirmed()
    {
        return $this->isConfirmed;
    }

    /**
     * Set roboPromo.
     *
     * @param int $roboPromo
     *
     * @return User
     */
    public function setRoboPromo($roboPromo)
    {
        $this->roboPromo = $roboPromo;

        return $this;
    }

    /**
     * Get roboPromo.
     *
     * @return int
     */
    public function getRoboPromo(): ?int
    {
        return $this->roboPromo;
    }

    /**
     * Set dateCreate.
     *
     * @param \DateTime $dateCreate
     *
     * @return User
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
     * Set dateLastAuth.
     *
     * @param \DateTime|null $dateLastAuth
     *
     * @return User
     */
    public function setDateLastAuth($dateLastAuth = null)
    {
        $this->dateLastAuth = $dateLastAuth;

        return $this;
    }

    /**
     * Get dateLastAuth.
     *
     * @return \DateTime|null
     */
    public function getDateLastAuth(): ?\DateTime
    {
        return $this->dateLastAuth;
    }

    /**
     * Set isBeginner.
     *
     * @param bool $isBeginner
     *
     * @return User
     */
    public function setIsBeginner($isBeginner)
    {
        $this->isBeginner = $isBeginner;

        return $this;
    }

    /**
     * Get isBeginner.
     *
     * @return bool
     */
    public function getIsBeginner()
    {
        return $this->isBeginner;
    }

    /**
     * Set referral.
     *
     * @param User $_referral
     *
     * @return $this;
     */
    public function setReferral($_referral)
    {
        $this->referral = $_referral;

        return $this;
    }

    /**
     * @return User
     */
    public function getReferral(): ?User
    {
        return $this->referral;
    }

    /**
     * Set tariff.
     *
     * @param \Office\Entity\Tariff|null $tariff
     *
     * @return User
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
     * Add company.
     *
     * @param \Office\Entity\Company $company
     *
     * @return User
     */
    public function addCompany(\Office\Entity\Company $company)
    {
        $this->company[] = $company;

        return $this;
    }

    /**
     * Remove company.
     *
     * @param \Office\Entity\Company $company
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeCompany(\Office\Entity\Company $company)
    {
        return $this->company->removeElement($company);
    }

    /**
     * Get company.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param string $password
     *
     * @return User
     */
    public function setNewPassword(string $password): User
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT);

        return $this;
    }

    /**
     * @return array
     */
    public function getRbacRoles(): array
    {
        $roles = $this->getUserRoles();
        $rbacRoles = [];

        foreach ($roles as $role) {
            $rbacRoles[] = new \Zend\Permissions\Rbac\Role($role->getRoleName());
        }

        return $rbacRoles;
    }

    /**
     * @return string|null
     */
    public function getUsername(): string
    {
        return $this->getEmail().'';
    }

    /**
     * @return Role[]
     */
    public function getUserRoles(): array
    {
        return $this->userRole->toArray();
    }

    /**
     * @return PersistentCollection
     */
    public function getUserRoleManager()
    {
        return $this->userRole;
    }

    public function getFIO(): string
    {
        return $this->getLastName().' '.$this->getFirstName().' '.$this->getMiddleName();
    }

    public function getDetail(string $name, $default = null)
    {
        // TODO: Implement getDetail() method.
    }

    public function getDetails(): array
    {
        // TODO: Implement getDetails() method.
    }

    public function getIdentity(): string
    {
        // TODO: Implement getIdentity() method.
    }

    public function getRoles(): iterable
    {
        // TODO: Implement getRoles() method.
    }
}
