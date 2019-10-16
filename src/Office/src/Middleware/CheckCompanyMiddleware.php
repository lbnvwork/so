<?php
declare(strict_types=1);

namespace Office\Middleware;

use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Office\Entity\Company;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Zend\Diactoros\Response;
use Zend\Expressive\Authentication\UserInterface;

/**
 * Class CheckCompanyMiddleware
 *
 * @package Office\Middleware
 */
class CheckCompanyMiddleware implements ServerMiddlewareInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * CheckCompanyMiddleware constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $handler): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute(UserInterface::class);
        $id = $request->getAttribute('id');
        $company = null;
        if (empty($id)) {
            return (new Response())->withStatus(404);
        }

        /** @var Company $company */
        $company = $this->entityManager->getRepository(Company::class)->findOneBy(['id' => $id]);
        if (!$company->getUser()->offsetExists($user->getId())) {
            $company = null;
        }

        if ($company === null || $company->getIsDeleted()) {
            return (new Response())->withStatus(404);
        }


        return $handler->handle($request->withAttribute(Company::class, $company));
    }
}
