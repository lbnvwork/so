<?php
declare(strict_types=1);

namespace Cms\Action\MoneyHistory;

use App\Service\FlashMessage;
use Cms\Action\AbstractAction;
use Office\Entity\Company;
use Office\Entity\MoneyHistory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Authentication\UserInterface;

/**
 * Class EditAction
 *
 * @package Cms\Action\MoneyHistory
 */
class EditAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::money-history/add';

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            $this->addMoney($request);

            return new RedirectResponse($this->urlHelper->generate('admin.moneyHistory'));
        }

        $companies = $this->entityManager->getRepository(Company::class)->findBy(['isDeleted' => false]);

        return new HtmlResponse($this->template->render(self::TEMPLATE_NAME, ['companies' => $companies]));
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addMoney(ServerRequestInterface $request): void
    {
        $params = $request->getParsedBody();

        /** @var Company $company */
        $company = $this->entityManager->getRepository(Company::class)->find($params['company']);

        $money = new MoneyHistory();
        $money->setType((int)$params['type'])
            ->setCompany($company)
            ->setSum($params['sum'])
            ->setTitle($params['title'])
            ->setDatetime(new \DateTime())
            ->setUser($request->getAttribute(UserInterface::class));
        $this->entityManager->persist($money);

        $sum = $money->getType() === MoneyHistory::TYPE_IN ? $money->getSum() : $money->getSum() * -1;
        $company->setBalance($company->getBalance() + $sum);

        $this->entityManager->flush();

        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);
        $flashMessage->addSuccessMessage('Данные сохранены');
    }
}
