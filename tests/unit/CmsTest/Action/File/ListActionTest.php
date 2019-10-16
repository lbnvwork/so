<?php
declare(strict_types=1);

namespace CmsTest\Action\File;

use App\Helper\UrlHelper;
use Cms\Action\File\ListAction;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class ListActionTest extends TestCase
{
    public function testProcess(): void
    {
        $template = $this->prophesize(TemplateRendererInterface::class);
        $template->render(Argument::type('string'), Argument::type('array'))->willReturn('test');

        $entityManager = $this->prophesize(EntityManager::class);
        $repository = $this->prophesize(EntityRepository::class);
        $entityManager->getRepository(Argument::type('string'))->willReturn($repository->reveal());
        $urlHelper = $this->prophesize(UrlHelper::class);

        $action = new ListAction(
            $entityManager->reveal(),
            $template->reveal(),
            $urlHelper->reveal()
        );

        /** @var ResponseInterface $response */
        $response = $action->process(
            $this->prophesize(ServerRequestInterface::class)->reveal(),
            $this->prophesize(RequestHandlerInterface::class)->reveal()
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('test', $response->getBody()->getContents());
    }
}
