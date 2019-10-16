<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 19.01.18
 * Time: 10:46
 */

namespace Auth\Service;

use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Expressive\Authentication\AuthenticationInterface;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Session\LazySession;
use Zend\Expressive\Session\SessionMiddleware;

/**
 * Class AuthenticationService
 *
 * @package Auth\Service
 */
class AuthenticationService implements AuthenticationInterface
{
    public const SESSION_AUTH = 'auth_user';

    /** @var EntityManager */
    private $entityManager;

    /**
     * @var User|null
     */
    private $user;

    /**
     * AuthenticationService constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return null|UserInterface
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        /** @var LazySession $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        if ($session->has(self::SESSION_AUTH)) {
            $this->user = $this->entityManager->find(User::class, $session->get(self::SESSION_AUTH));
        }

        return $this->user;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        return (new Response())->withStatus(401);
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }
}
