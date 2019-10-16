<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 10.04.18
 * Time: 8:14
 */

namespace ApiAtolV1\Action;

use ApiV1\Service\TokenService;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\ApiKey;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class TokenAction
 *
 * @package ApiAtolV1\Action
 */
class TokenAction implements ServerMiddlewareInterface
{
    private $entityManager;

    /**
     * @var TokenService
     */
    private $tokenService;

    /**
     * TokenAction constructor.
     *
     * @param EntityManager $entityManager
     * @param TokenService $tokenService
     */
    public function __construct(EntityManager $entityManager, TokenService $tokenService)
    {
        $this->entityManager = $entityManager;
        $this->tokenService = $tokenService;
    }

    /**
     * Получение апи ключа
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|\Zend\Diactoros\Response|JsonResponse|static
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            $params = json_decode($request->getBody()->getContents(), true);
        } else {
            $params = $request->getQueryParams();
        }
        if (empty($params['login']) || empty($params['pass'])) {
            return (new JsonResponse(
                [
                    'text'  => 'Неверный логин или пароль',
                    'token' => null,
                    'code'  => Code::INCORRECT_LOGIN
                ]
            ))->withStatus(400);
        }
        /** @var ApiKey $apiKey */
        $apiKey = $this->entityManager->getRepository(ApiKey::class)->findOneBy(
            [
                'login'    => $params['login'],
                'password' => hash('ripemd128', $params['pass'])
            ]
        );
        if ($apiKey === null) {
            return (new JsonResponse(
                [
                    'text'  => 'Неверный логин или пароль',
                    'token' => null,
                    'code'  => Code::INCORRECT_LOGIN
                ]
            ))->withStatus(400);
        }


        $newKey = $this->tokenService->checkToken($apiKey);

        if ($newKey) {
            return new JsonResponse(
                [
                    'text'  => null,
                    'code'  => Code::USE_NEW_TOKEN,
                    'token' => $apiKey->getToken()
                ]
            );
        }

        return new JsonResponse(
            [
                'text'  => null,
                'code'  => Code::USE_OLD_TOKEN,
                'token' => $apiKey->getToken()
            ]
        );
    }
}
