<?php

namespace ApiV1Test\Middleware;

use ApiV1\Action\Code;
use ApiV1\Middleware\ProcessingMiddleware;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Office\Entity\Processing;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;
use Zend\Json\Json;

class ProcessingMiddlewareTest extends TestCase
{
    public function testEmptyProcessingId(): void
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var RequestHandlerInterface $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findOneBy')
            ->willReturn(null);

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $middleware = new ProcessingMiddleware($entityManager);
        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $json = Json::decode($response->getBody()->getContents(), Json::TYPE_ARRAY);
        $this->assertArrayHasKey('code', $json);
        $this->assertArrayHasKey('message', $json);
        $this->assertEquals($json['code'], Code::INCORRECT_PROCESSING_ID);
        $this->assertEquals($json['message'], Code::getMessage(Code::INCORRECT_PROCESSING_ID));
    }

    public function testProcessingId(): void
    {
        $request = $this->getMockBuilder(ServerRequest::class)
            ->setMethods()
            ->disableOriginalConstructor()
            ->getMock();

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturnCallback(
            function ($param) {
                /** @var ServerRequestInterface $param */
                $this->assertInstanceOf(ServerRequestInterface::class, $param);
                $this->assertNotNull($param->getAttribute(Processing::class));

                return $this->createMock(ResponseInterface::class);
            }
        );

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findOneBy')
            ->willReturn(new Processing());

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $middleware = new ProcessingMiddleware($entityManager);
        $middleware->process($request, $handler);
    }

    public function testExternalId(): void
    {
        $request = $this->getMockBuilder(ServerRequest::class)
            ->setMethods(
                [
                    'getQueryParams',
                    'withAttribute',
                    'getAttribute',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $request->method('getQueryParams')->willReturn(['external_id' => 'test']);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $request->method('withAttribute')->willReturnCallback(
            function ($param, $value) {
                $this->assertEquals(Processing::class, $param);
                $this->assertInstanceOf(Processing::class, $value);

                return $this->createMock(ServerRequest::class);
            }
        );

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findOneBy')
            ->willReturn(null, new Processing());

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $middleware = new ProcessingMiddleware($entityManager);
        $middleware->process($request, $handler);
    }
}
