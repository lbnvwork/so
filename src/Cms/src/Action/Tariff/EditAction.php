<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 25.01.18
 * Time: 14:07
 */

namespace Cms\Action\Tariff;

use Cms\Action\AbstractAction;
use Office\Entity\Tariff;
use App\Service\FlashMessage;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class EditAction
 *
 * @package Cms\Action
 */
class EditAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::tariff/edit';

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|Response|HtmlResponse|static
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        $id = $request->getAttribute('id');
        $tariff = null;
        if (empty($id)) {
            $tariff = new Tariff();
        } else {
            $tariff = $this->entityManager->find(Tariff::class, $id);
        }

        if ($tariff === null) {
            return (new Response())->withStatus(404);
        }

        if ($request->getMethod() === 'POST') {
            return $this->saveService($request, $tariff);
        }

        return new HtmlResponse($this->template->render(self::TEMPLATE_NAME, ['tariff' => $tariff]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param Tariff $tariff
     *
     * @return Response\RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveService(ServerRequestInterface $request, Tariff $tariff)
    {
        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);
        $params = $request->getParsedBody();
        $tariff->setSort($params['sort']);
        $tariff->setTitle($params['title']);
        $tariff->setDescription($params['description']);
        $tariff->setRentCost(empty($params['rent_cost']) ? null : $params['rent_cost']);
        $tariff->setTurnoverPercent((empty($params['turnover_percent'])) ? null : $params['turnover_percent']);
        $tariff->setMonthLimit(empty($params['month_limit']) ? null : $params['month_limit']);
        $tariff->setMonthCount(empty($params['month_count']) ? null : $params['month_count']);
        $tariff->setMaxTurnover(empty($params['max_turnover']) ? null : $params['max_turnover']);
        $tariff->setIsPopular(isset($params['popular']));
        $tariff->setIsDefault(isset($params['default']));
        $tariff->setIsBeginner(isset($params['beginner']))
            ->setFnliveTime($params['fn_live_time']);
        $tariff->setIsPromotime(isset($params['promotime']));
        $this->entityManager->persist($tariff);
        $this->entityManager->flush();

        if ($tariff->isDefault()) {
            $this->entityManager->getRepository(Tariff::class)
                ->createQueryBuilder('t')
                ->update()
                ->set('t.isDefault', 0)
                ->where('t.id <> :id')->setParameter('id', $tariff->getId())
                ->getQuery()
                ->getResult();
        }

        $flashMessage->addSuccessMessage('Данные сохранены');

        return new Response\RedirectResponse($this->urlHelper->generate('admin.tariff.edit', ['id' => $tariff->getId()]));
    }
}
