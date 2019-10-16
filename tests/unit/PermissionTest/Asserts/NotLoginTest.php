<?php
declare(strict_types=1);


namespace PermissionTest\Asserts;

use Auth\Entity\User;
use Permission\Asserts\NotLogin;
use PHPUnit\Framework\TestCase;
use Zend\Permissions\Rbac\Rbac;

class NotLoginTest extends TestCase
{
    public function testAssertTrue()
    {
        $class = new NotLogin();
        /** @var Rbac $rbac */
        $rbac = $this->prophesize(Rbac::class)->reveal();
        $this->assertTrue($class->assert($rbac));
    }

    public function testAssertFalse()
    {
        /** @var User $user */
        $user = $this->prophesize(User::class)->reveal();
        $class = new NotLogin($user);
        /** @var Rbac $rbac */
        $rbac = $this->prophesize(Rbac::class)->reveal();
        $this->assertFalse($class->assert($rbac));
    }
}