<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 09.04.18
 * Time: 12:49
 */
declare(strict_types=1);

namespace Cms\Action\Company;

use Cms\Action\AbstractAction;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Office\Entity\Company;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class DeleteAction
 *
 * @package Cms\Action\Company
 */
class DeleteAction extends AbstractAction
{
    public const TEMPLATE_NAME = 'admin::company/edit';

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface|Response|HtmlResponse|static
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        if ($request->getMethod() === 'DELETE') {
            /** @var Company $company */
            $company = $this->entityManager->getRepository(Company::class)->findOneBy(['id' => $request->getAttribute('id')]);
            if ($company !== null) {
                $company->setIsDeleted(true);
                $this->entityManager->persist($company);
                $this->entityManager->flush();
            }
        }

        return new Response\JsonResponse([]);
    }
}
