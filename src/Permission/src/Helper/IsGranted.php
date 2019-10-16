<?php
declare(strict_types=1);

namespace Permission\Helper;

use Auth\Service\AuthenticationService;
use Zend\Permissions\Rbac\Rbac;

/**
 * Class IsGranted
 *
 * @package Permission\Helper
 */
class IsGranted
{
    /**
     * @var Rbac
     */
    private $rbac;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * IsGranted constructor.
     *
     * @param Rbac $rbac
     * @param AuthenticationService $authenticationService
     */
    public function __construct(Rbac $rbac, AuthenticationService $authenticationService)
    {
        $this->rbac = $rbac;
        $this->authenticationService = $authenticationService;
    }

    public function __invoke(string $permission)
    {
        $roles = $this->authenticationService->getUser()->getUserRoles();

        foreach ($roles as $role) {
            if ($this->rbac->isGranted($role->getRoleName(), $permission)) {
                return true;
            }
        }

        return false;
    }
}
