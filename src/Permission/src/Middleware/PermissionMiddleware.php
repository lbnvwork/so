<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 22.01.18
 * Time: 10:35
 */

namespace Permission\Middleware;

use App\Helper\UrlHelper;
use Auth\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Router\RouteResult;
use Zend\Permissions\Rbac\Rbac;
use Zend\Permissions\Rbac\Role;

/**
 * Class PermissionMiddleware
 *
 * @package Permission\Middleware
 */
class PermissionMiddleware implements MiddlewareInterface
{
    protected $rbac;

    protected $asserts = [];

    private $urlHelper;

    /**
     * PermissionMiddleware constructor.
     *
     * @param Rbac $rbac
     * @param UrlHelper $urlHelper
     * @param array $asserts
     */
    public function __construct(Rbac $rbac, UrlHelper $urlHelper, array $asserts = [])
    {
        $this->rbac = $rbac;
        $this->urlHelper = $urlHelper;
        $this->asserts = $asserts;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $assert = null;
        /** @var User $user */
        $user = $request->getAttribute(UserInterface::class);
        $route = $request->getAttribute(RouteResult::class);
        if ($route && $route->getMatchedRoute()) {
            $routeName = $route->getMatchedRoute()->getName();

            if (isset($this->asserts[$routeName])) {
                $assert = new $this->asserts[$routeName]($user);
            }

            $roles = $user ? $user->getRbacRoles() : [new Role('guest')];

            $isGranted = false;
            foreach ($roles as $role) {
                if ($this->rbac->isGranted($role, $routeName, $assert ?? null)) {
                    $isGranted = true;
                    break;
                }
            }

            if (!$isGranted) {
                if ($user === null) {
                    return new RedirectResponse($this->urlHelper->generate('login'));
                }
                if ($routeName === 'login' || $routeName === 'register') {
                    return new RedirectResponse($this->urlHelper->generate('office.billing'));
                }

                return new EmptyResponse(403);
            }
        }

        return $handler->handle($request);
    }
}
