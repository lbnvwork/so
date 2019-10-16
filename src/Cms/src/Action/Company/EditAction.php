<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 09.04.18
 * Time: 12:49
 */

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
 * Class EditAction
 *
 * @package Cms\Action\Company
 */
class EditAction extends AbstractAction
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
        /** @var Company $company */
        $company = $this->entityManager->getRepository(Company::class)->findOneBy(['id' => $request->getAttribute('id')]);
        if ($company === null) {
            return (new Response())->withStatus(404);
        }

        return new HtmlResponse($this->template->render(self::TEMPLATE_NAME, ['company' => $company]));
    }
}
