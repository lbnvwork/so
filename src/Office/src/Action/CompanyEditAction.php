<?php

namespace Office\Action;

use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\Company;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Router;
use Zend\Expressive\Template;

/**
 * Class CompanyEditAction
 * @package Office\Action
 */
class CompanyEditAction implements ServerMiddlewareInterface
{
    private $entityManager;

    private $template;

    public function __construct(EntityManager $entityManager, Template\TemplateRendererInterface $template)
    {
        $this->entityManager = $entityManager;
        $this->template = $template;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute(UserInterface::class);
        $id = $request->getAttribute('id');
        $company = null;
        if (empty($id)) {
            $company = new Company();
            $company->addUser($user);
        } else {
            $company = $this->entityManager->getRepository(Company::class)->findOneBy(['id' => $id, 'user' => $user]);
        }

        if ($company === null) {
            return (new Response())->withStatus(404);
        }

        return new HtmlResponse($this->template->render('office::company/edit', ['company' => $company]));
    }
}
