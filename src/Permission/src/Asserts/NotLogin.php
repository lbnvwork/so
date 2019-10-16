<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 26.01.18
 * Time: 10:21
 */

namespace Permission\Asserts;

use Auth\Entity\User;
use Zend\Permissions\Rbac\AssertionInterface;
use Zend\Permissions\Rbac\Rbac;

/**
 * Class IsAuth
 *
 * @package Permission\Asserts
 */
class NotLogin implements AssertionInterface
{
    private $user;

    /**
     * Login constructor.
     *
     * @param User|null $user
     */
    public function __construct(?User $user = null)
    {
        $this->user = $user;
    }

    public function assert(Rbac $rbac): bool
    {
        return $this->user === null;
    }
}
