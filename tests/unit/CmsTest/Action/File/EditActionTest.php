<?php
declare(strict_types=1);

namespace CmsTest\Action\File;

use App\Entity\File;
use App\Helper\UrlHelper;
use Cms\Action\File\EditAction;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class EditActionTest extends TestCase
{
    public function testProcess404()
    {
        $template = $this->prophesize(TemplateRendererInterface::class);
        $template->render(Argument::type('string'), Argument::type('array'))->willReturn('test');

        $entityManager = $this->prophesize(EntityManager::class);
        $repository = $this->prophesize(EntityRepository::class);
        $entityManager->getRepository(Argument::type('string'))->willReturn($repository->reveal());

        $action = new EditAction(
            $entityManager->reveal(),
            $template->reveal(),
            $this->prophesize(UrlHelper::class)->reveal()
        );

        /** @var ServerRequestInterface|ObjectProphecy $request */
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(Argument::any())->willReturn(1);
        $request->getMethod()->willReturn('GET');

        /** @var ResponseInterface $response */
        $response = $action->process(
            $request->reveal(),
            $this->prophesize(RequestHandlerInterface::class)->reveal()
        );

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testProcessMethodGet()
    {
        $template = $this->prophesize(TemplateRendererInterface::class);
        $template->render(Argument::type('string'), Argument::type('array'))->willReturn('test');

        $entityManager = $this->prophesize(EntityManager::class);
        $repository = $this->prophesize(EntityRepository::class);
        $repository->findOneBy(Argument::type('array'))->willReturn(new File());
        $repository->findAll()->willReturn([]);
        $entityManager->getRepository(Argument::type('string'))->willReturn($repository->reveal());

        $action = new EditAction(
            $entityManager->reveal(),
            $template->reveal(),
            $this->prophesize(UrlHelper::class)->reveal()
        );

        /** @var ResponseInterface $response */
        $response = $action->process(
            $this->prophesize(ServerRequestInterface::class)->reveal(),
            $this->prophesize(RequestHandlerInterface::class)->reveal()
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('test', $response->getBody()->getContents());
    }

    public function testProcessMethodPost()
    {
        $template = $this->prophesize(TemplateRendererInterface::class);
        $template->render(Argument::type('string'), Argument::type('array'))->willReturn('test');

        $entityManager = $this->prophesize(EntityManager::class);
        $repository = $this->prophesize(EntityRepository::class);
        $repository->findOneBy(Argument::type('array'))->willReturn(new File());
        $entityManager->getRepository(Argument::type('string'))->willReturn($repository->reveal());

        /** @var UrlHelper|ObjectProphecy $urlHelper */
        $urlHelper = $this->prophesize(UrlHelper::class);
        $urlHelper->generate(Argument::any(), Argument::any())->willReturn('url');

        $action = new EditAction(
            $entityManager->reveal(),
            $template->reveal(),
            $urlHelper->reveal()
        );

        /** @var ServerRequestInterface|ObjectProphecy $request */
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(Argument::any())->willReturn(1);
        $request->getMethod()->willReturn('POST');
        $request->getUploadedFiles()->willReturn([]);

        /** @var ResponseInterface $response */
        $response = $action->process(
            $request->reveal(),
            $this->prophesize(RequestHandlerInterface::class)->reveal()
        );

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
}
