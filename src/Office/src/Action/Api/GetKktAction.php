<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 03.04.18
 * Time: 19:30
 */

namespace Office\Action\Api;

use App\Entity\Service;
use App\Service\DateTime;
use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\Kkt;
use Office\Service\Umka;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Authentication\UserInterface;

/**
 * Class GetKktAction
 *
 * @package Office\Action\Api
 */
class GetKktAction implements ServerMiddlewareInterface
{
    private $entityManager;

    private $umka;

    /**
     * GetKktAction constructor.
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
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $messages = [];
        /** @var Kkt $kkt */
        $kkt = $request->getAttribute(Kkt::class);
        /** @var User $user */
        $user = $request->getAttribute(UserInterface::class);
        if ($user->getId() === User::TEST_USER_ID) {
            $messages['danger'] = ['Недостаточно прав'];
        }

        if (\count($messages) === 0) {
            if ($request->getMethod() === 'POST') {
                if ($kkt->getIsEnabled()) {
                    $messages['danger'] = ['Касса уже установлена'];
                } else {
                    $messages = $this->getKkt($kkt, $user);
                }
            } elseif ($request->getMethod() === 'DELETE') {
                try {
                    $deleteOk = true;
                    if ($kkt->getSerialNumber()) {
                        $deleteOk = $this->umka->removeKkt($kkt->getSerialNumber());
                    }

                    if ($deleteOk) {
                        //TODO Не хорошо так делать...
                        $params = json_decode(file_get_contents('php://input'), true);
                        $messages['success'] = ['Касса успешно удалена'];
                        $kkt->setIsEnabled(false)
                            ->setIsDeleted(true)
                            ->setDateDeleted(new DateTime())
                            ->setIsSendFn($params['isSend'] === 'true');
                        $this->entityManager->flush();
                    } else {
                        $messages['danger'] = ['Ошибка сервиса, попробуйте позже'];
                    }
                } catch (\Exception $e) {
                    $messages['danger'] = ['Ошибка сервиса, попробуйте позже '];
                }
            }
        }

        return new JsonResponse(
            [
                'messages' => $messages,
                'success'  => isset($messages['success']),
                'balance'  => $kkt->getShop()->getCompany()->getBalance(),
                'kkt'      => $kkt
            ]
        );
    }

    /**
     * @param Kkt $kkt
     * @param User $user
     *
     * @return array
     */
    public function getKkt(Kkt $kkt, User $user)
    {
        $messages = [];

        $tariff = $kkt->getTariff();
        $price = $tariff->getRentCost();
        $minMonth = $tariff->getMonthCount();

        $servicePrice = 0;
        /** @var Service $item */
        foreach ($tariff->getService() as $item) {
            $servicePrice += $item->getPrice();
        }

        $minPrice = $price * $minMonth + $servicePrice;

        if ($kkt->getShop()->getCompany()->getBalance() < $minPrice) {
            $messages['danger'] = ['Низкий баланс, пополните счет'];

            return $messages;
        }

        try {
            $kkms = $this->umka->getAllNotRegistredKkt();
            if (\count($kkms) > 0) {
                $kkm = null;
                $fnLiveTime = $tariff->getFnliveTime();
                foreach ($kkms as $k) {
                    if ($k['status']['linked'] === true
                        && isset($k['status']['fsStatus']['fsNumber'], $k['eklzProp']['liveTimeMonth'])
                        && $k['serialNo'] != 17000675
//                        && $fnLiveTime == $k['eklzProp']['liveTimeMonth']  @TODO Afinogen работает не корректно
                    ) {
                        //Проверяем не занята ли касса
                        if ($this->entityManager->getRepository(Kkt::class)->findOneBy(
                            [
                                    'serialNumber' => $k['serialNo'],
                            //                                    'fsNumber'     => $k['status']['fsStatus']['fsNumber']
                                ]
                        ) === null) {
                            $kkm = $k;
                            break;
                        }
                    }
                }
                $beginner = $user->getIsBeginner();
                if ($kkm !== null) {
                    $dateExpiried = (new DateTime())->addMonths($minMonth);
                    $promotime = $kkt->getTariff()->getIsPromotime();
                    if ($promotime && $beginner) {
                        $dateExpiried = (new DateTime('first day of next month'))->addMonths($minMonth);
                    }
                    $kkt->setSerialNumber($kkm['serialNo'])
                        ->setInn($kkm['inn'])
                        ->setRegNumber($kkm['regNo'])
                        ->setFsNumber($kkm['status']['fsStatus']['fsNumber'])
                        ->setFsVersion($kkm['status']['fsStatus']['fsVersion'])
                        ->setIsFiscalized($kkm['status']['fiscalized'])
                        ->setRawData(json_encode($kkm))
                        ->setIsEnabled(true)
                        ->setDateExpired($dateExpiried)
                        ->setTariffDateStart(new DateTime());

                    $this->entityManager->persist($kkt);

                    $kkt->getShop()->getCompany()->setBalance($kkt->getShop()->getCompany()->getBalance() - $minPrice);
                    $this->entityManager->persist($kkt->getShop()->getCompany());
                    if ($beginner) {
                        $user->setIsBeginner(false);
                    }
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                    $messages['success'] = ['Касса успешно установлена'];
                } else {
                    $messages['danger'] = ['Нет доступных касс, обратитесь к администратору'];
                }
            } else {
                $messages['danger'] = ['Нет доступных касс совсем, обратитесь к администратору'];
            }
//                } else {
//                    $messages['danger'] = ['Ошибка сервиса, обратитесь к администратору'];
//                }
//            } else {
//                $messages['danger'] = ['Нет доступных касс, обратитесь к администратору'];
//            }
        } catch (\Exception $e) {
            $messages['danger'] = ['Ошибка сервиса, попробуйте позже'.$e->getMessage()];
        }

        return $messages;
    }
}
