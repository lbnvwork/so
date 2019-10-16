<?php
declare(strict_types=1);

namespace ApiV1\Action;

use ApiV1\Service\TokenService;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\ApiKey;
use Zend\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Json\Json;

/**
 * Class TokenAction
 *
 * @package ApiV1\Action
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
        try {
            $params = Json::decode($request->getBody()->getContents(), Json::TYPE_ARRAY);
        } catch (\Exception $e) {
            return (new JsonResponse(
                [
                    'message' => Code::getMessage(Code::INCORECT_DATA).' '.$e->getMessage(),
                    'token'   => null,
                    'code'    => Code::INCORECT_DATA,
                ]
            ))->withStatus(400);
        }
        if (empty($params['login']) || empty($params['password'])) {
            return (new JsonResponse(
                [
                    'message' => Code::getMessage(Code::INCORRECT_LOGIN),
                    'token'   => null,
                    'code'    => Code::INCORRECT_LOGIN,
                ]
            ))->withStatus(400);
        }
        /** @var ApiKey $apiKey */
        $apiKey = $this->entityManager->getRepository(ApiKey::class)->findOneBy(
            [
                'login'    => $params['login'],
                'password' => hash('ripemd128', (string)$params['password']),
            ]
        );
        if ($apiKey === null) {
            return (new JsonResponse(
                [
                    'message' => Code::getMessage(Code::INCORRECT_LOGIN),
                    'token'   => null,
                    'code'    => Code::INCORRECT_LOGIN,
                ]
            ))->withStatus(400);
        }

        if ($this->tokenService->checkToken($apiKey)) {
            return new JsonResponse(
                [
                    'message' => null,
                    'code'    => Code::USE_NEW_TOKEN,
                    'token'   => $apiKey->getToken(),
                ]
            );
        }

        return new JsonResponse(
            [
                'message' => null,
                'code'    => Code::USE_OLD_TOKEN,
                'token'   => $apiKey->getToken(),
            ]
        );
    }
}
