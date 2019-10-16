<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 10.04.18
 * Time: 11:51
 */

namespace Office\Action\Api;

use Auth\Entity\User;
use Auth\UserRepository\Database;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\ApiKey;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Authentication\UserInterface;

/**
 * Class GetAccessAction
 * @package Office\Action\Api
 */
class GetAccessAction implements ServerMiddlewareInterface
{
    private $entityManager;

    /**
     * GetAccessAction constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute(UserInterface::class);
        $login = explode('@', $user->getEmail())[0].'_'.mt_rand(1, 100);
        $password = Database::generateStrongPassword();

        $apiKey = $this->entityManager->getRepository(ApiKey::class)->findOneBy(['user' => $user]);
        if ($apiKey === null) {
            $apiKey = new ApiKey($user);
        }

        if ($user->getId() === User::TEST_USER_ID) {
            $login = 'test_api';
            $password = '123456';
        }

        $apiKey->setLogin($login)
            ->setPassword(hash('ripemd128', $password));
        $this->entityManager->persist($apiKey);
        $this->entityManager->flush();

        return new JsonResponse(['login' => $login, 'password' => $password]);
    }
}
