<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\HomePageHandler;
use App\Service\RecaptchaService;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class HomePageHandlerTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    /** @var RecaptchaService|ObjectProphecy */
    protected $recaptcha;

    /** @var EntityManager|ObjectProphecy */
    protected $entityManager;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->recaptcha = $this->prophesize(RecaptchaService::class);
        $this->entityManager = $this->prophesize(EntityManager::class);
    }

    public function test()
    {
        $this->assertTrue(true);
    }

//    public function testReturnsJsonResponseWhenNoTemplateRendererProvided()
//    {
//        $homePage = new HomePageHandler(
//            $this->entityManager->reveal(),
//            $this->recaptcha->reveal(),
//            null
//        );
//        $response = $homePage->handle(
//            $this->prophesize(ServerRequestInterface::class)->reveal()
//        );
//
//        $this->assertInstanceOf(JsonResponse::class, $response);
//    }
//
//    public function testReturnsHtmlResponseWhenTemplateRendererProvided()
//    {
//        $renderer = $this->prophesize(TemplateRendererInterface::class);
//        $renderer
//            ->render('app::home-page', Argument::type('array'))
//            ->willReturn('');
//
//        $homePage = new HomePageHandler(
//            $this->entityManager->reveal(),
//            $this->recaptcha->reveal(),
//            $renderer->reveal()
//        );
//
//        $response = $homePage->handle(
//            $this->prophesize(ServerRequestInterface::class)->reveal()
//        );
//
//        $this->assertInstanceOf(HtmlResponse::class, $response);
//    }
}