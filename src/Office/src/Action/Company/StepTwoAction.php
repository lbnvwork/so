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
use Office\Entity\Ofd;
use Office\Service\SendMail;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Template;

/**
 * Class StepTwoAction
 *
 * @package Office\Action\Company
 */
class StepTwoAction implements ServerMiddlewareInterface
{
    private $entityManager;

    private $template;

    private $urlHelper;

    private $sendMail;

    /**
     * StepTwoAction constructor.
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
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): \Psr\Http\Message\ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute(UserInterface::class);
        /** @var Company $company */
        $company = $request->getAttribute(Company::class);

        if ($request->getMethod() === 'POST') {
            /** @var FlashMessage $flashMessage */
            $flashMessage = $request->getAttribute(FlashMessage::class);
            $params = $request->getParsedBody();

            $ofd = $this->entityManager->getRepository(Ofd::class)->findOneBy(
                [
                    'id'        => $params['ofdId'],
                    'isEnabled' => true
                ]
            );
            if ($ofd === null) {
                $flashMessage->addErrorMessage('Не корректный ОФД');

                return new Response\RedirectResponse($this->urlHelper->generate('office.company.stepTwo', ['id' => $company->getId()]));
            }

            $company->setOfd($ofd);
            if ($user->getId() !== User::TEST_USER_ID) {
                $this->entityManager->persist($company);
                $this->entityManager->flush();
            }

            $flashMessage->addSuccessMessage('Данные сохранены');

            return new Response\RedirectResponse($this->urlHelper->generate('office.company.stepThree', ['id' => $company->getId()]));
        }

        $ofds = $this->entityManager->getRepository(Ofd::class)->findBy(['isEnabled' => 1]);

        return new HtmlResponse(
            $this->template->render(
                'office::company/step-two',
                [
                    'ofds'    => $ofds,
                    'company' => $company
                ]
            )
        );
    }
}
