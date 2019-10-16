<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 22.03.18
 * Time: 19:56
 */

namespace Office\Action\Company;

use App\Helper\UrlHelper;
use App\Service\FlashMessage;
use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\Company;
use Office\Service\SendMail;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Template;

/**
 * Class StepOneAction
 *
 * @package Office\Action\company
 */
class StepOneAction implements ServerMiddlewareInterface
{
    private $entityManager;

    private $template;

    private $urlHelper;

    private $sendMail;

    /**
     * StepOneAction constructor.
     *
     * @param EntityManager $entityManager
     * @param Template\TemplateRendererInterface $template
     * @param UrlHelper $urlHelper
     * @param SendMail $sendMail
     */
    public function __construct(EntityManager $entityManager, Template\TemplateRendererInterface $template, UrlHelper $urlHelper, SendMail $sendMail)
    {
        $this->entityManager = $entityManager;
        $this->template = $template;
        $this->urlHelper = $urlHelper;
        $this->sendMail = $sendMail;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|Response|HtmlResponse|Response\RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        /** @var FlashMessage $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessage::class);
        /** @var User $user */
        $user = $request->getAttribute(UserInterface::class);
        $id = $request->getAttribute('id');
        $company = null;
        if (empty($id)) {
            $company = new Company();
            $company->addUser($user);
        } else {
            /** @var Company $company */
            $company = $this->entityManager->getRepository(Company::class)->findOneBy(['id' => $id]);
            if (!$company->getUser()->offsetExists($user->getId())) {
                $company = null;
            }
        }

        if ($company === null || $company->getIsDeleted()) {
            return (new Response())->withStatus(404);
        }

        if ($request->getMethod() === 'POST') {
            $params = $request->getParsedBody();
            if (empty($params)
                || ($company->getId() === 0
                    && !$this->entityManager->getRepository(Company::class)->findOneBy(
                        [
                            'inn'  => $params['inn'],
                            'user' => $user
                        ]
                    ))) {
                $flashMessage->addErrorMessage('Компания с таким ИНН уже зарегистрирована');

                return new Response\RedirectResponse($this->urlHelper->generate('office.company.stepOne', ['id' => $company->getId()]));
            }

            foreach ($params as $key => $param) {
                if ($key === 'companyAcept') {
                    continue;
                }
                $method = 'set'.ucfirst($key);
                $company->{$method}($param);
            }

            if ($company->getId() === null) {
                $user->addCompany($company);
//                $company->addUser($user);
                $this->entityManager->persist($user);
            }
            $this->entityManager->persist($company);

            if ($company->getId() === null) {
                $this->sendMail->sendRegisterNewCompany($company);
            }

            if ($user->getId() === User::TEST_USER_ID) {
                if ($company->getId()) {
                    return new Response\RedirectResponse($this->urlHelper->generate('office.company.stepTwo', ['id' => $company->getId()]));
                } else {
                    return new Response\RedirectResponse($this->urlHelper->generate('office.company.stepOne', ['id' => 0]));
                }
            }
            $this->entityManager->flush();

            $flashMessage->addSuccessMessage('Данные сохранены');

            return new Response\RedirectResponse($this->urlHelper->generate('office.company.stepTwo', ['id' => $company->getId()]));
        }

        return new HtmlResponse($this->template->render('office::company/step-one', ['company' => $company]));
    }
}
