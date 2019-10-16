<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 03.04.18
 * Time: 19:30
 */

namespace Office\Action\Api;

use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\Kkt;
use Office\Service\Umka;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Authentication\UserInterface;

/**
 * Class SetRnmAction
 *
 * @package Office\Action\Api
 */
class SetRnmAction implements ServerMiddlewareInterface
{
    private $entityManager;

    private $umka;

    /**
     * SetRnmAction constructor.
     *
     * @param EntityManager $entityManager
     * @param Umka $umka
     */
    public function __construct(EntityManager $entityManager, Umka $umka)
    {
        $this->entityManager = $entityManager;
        $this->umka = $umka;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        $messages = [];
        /** @var User $user */
        $user = $request->getAttribute(UserInterface::class);
        /** @var Kkt $kkt */
        $kkt = $request->getAttribute(Kkt::class);
        if ($user->getId() === User::TEST_USER_ID) {
            $messages['danger'] = ['Недостаточно прав'];
        }

        if (\count($messages) === 0) {
            if ($request->getMethod() === 'POST') {
                if (!$kkt->getIsEnabled()) {
                    $messages['danger'] = ['Касса не установлена'];
                } else {
//                    $messages = $this->getKkt($kkt);
                    $params = $request->getParsedBody();
                    if (!empty($params['rnm'])) {
                        if ($kkt->getRegNumber()) {
                            $messages['danger'] = ['РНМ уже записан'];
                        } else {
                            $kkt->setRegNumber($params['rnm']);

                            if ($this->umka->fiscalizeKkt($kkt)) {
                                $messages['success'] = ['РНМ сохранен, ожидайте активации'];
                            } else {
                                $messages['danger'] = ['Ошибка обработки запроса'];
                            }

                            $this->entityManager->flush();
                        }
                    } else {
                        $messages['danger'] = ['Не корректный РНМ'];
                    }
                }
            }
        }

        return new JsonResponse(
            [
                'messages' => $messages,
                'success'  => isset($messages['success']),
                'balance'  => $kkt->getShop()->getCompany()->getBalance(),
                'kkt'      => $kkt,
            ]
        );
    }
}
