<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\RecaptchaService;
use Doctrine\ORM\EntityManager;
use Office\Entity\Tariff;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

/**
 * Class HomePageHandler
 *
 * @package App\Handler
 */
class HomePageHandler implements RequestHandlerInterface
{
    public const TEMPLATE_NAME = 'app::home-page';

    private $template;

    private $recaptchaService;

    private $entityManager;

    /**
     * HomePageHandler constructor.
     *
     * @param EntityManager $entityManager
     * @param RecaptchaService $_recaptcha
     * @param Template\TemplateRendererInterface $template
     */
    public function __construct(EntityManager $entityManager, RecaptchaService $_recaptcha, Template\TemplateRendererInterface $template)
    {
        $this->entityManager = $entityManager;
        $this->template = $template;
        $this->recaptchaService = $_recaptcha;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Tariff[] $tariffs */
        $tariffs = $this->entityManager->getRepository(Tariff::class)->findBy([], ['sort' => 'ASC']);
        $defaultTariff = null;
        foreach ($tariffs as $tariff) {
            if ($tariff->isDefault()) {
                $defaultTariff = $tariff;
                break;
            }
        }

        return new HtmlResponse(
            $this->template->render(
                'app::home-page',
                [
                    'tariffs'       => $tariffs,
                    'defaultTariff' => $defaultTariff,
                    'layout'        => 'layout::landing',
                    'recaptcha'     => $this->recaptchaService->getRecaptcha()

                ]
            )
        );
    }
}
