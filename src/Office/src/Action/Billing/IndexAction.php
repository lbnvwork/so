<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 26.03.18
 * Time: 21:20
 */

namespace Office\Action\Billing;

use App\Helper\UrlHelper;
use App\Service\DateTime;
use Auth\Entity\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface as ServerMiddlewareInterface;
use Office\Entity\Company;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Template;

/**
 * Class IndexAction
 *
 * @package Office\Action\Billing
 */
class IndexAction implements ServerMiddlewareInterface
{
    private $entityManager;

    private $template;

    private $urlHelper;

    /**
     * IndexAction constructor.
     *
     * @param EntityManager $entityManager
     * @param Template\TemplateRendererInterface $template
     * @param UrlHelper $urlHelper
     */
    public function __construct(EntityManager $entityManager, Template\TemplateRendererInterface $template, UrlHelper $urlHelper)
    {
        $this->entityManager = $entityManager;
        $this->template = $template;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return \Psr\Http\Message\ResponseInterface|HtmlResponse
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : \Psr\Http\Message\ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute(UserInterface::class);

        $companies = $this->entityManager->getRepository(Company::class)->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->where('u = :user and c.isDeleted = 0')
            ->setParameter('user', $user)->getQuery()
            ->getResult();
        $service = $this->entityManager->getRepository(\App\Entity\Service::class)->find(2);
        $price = $service->getPrice();
        $curDate = new DateTime();
        $months = 0;
        foreach ($companies as &$company) {
            $kkts = $this->entityManager->createQueryBuilder()->select('k.dateExpired')->from(\Office\Entity\Shop::class, 's')->innerJoin('s.kkt', 'k')->where(
                'k.isEnabled = 1 AND s.company = :company AND k.dateExpired IS NOT NULL'
            )->setParameter('company', $company)->getQuery()->getArrayResult();
            foreach ($kkts as $kkt) {
                $dateExpired = $kkt['dateExpired'];
                if ($dateExpired > $curDate) {
                    $interval = $dateExpired->diff($curDate);
                    $months = $months + (int)$interval->format('%m');
                }
            }

            $company->balance2 = $months * $price + (int)$company->getBalance();
        }


        return new HtmlResponse($this->template->render('office::billing/index', ['companies' => $companies]));
    }
}
