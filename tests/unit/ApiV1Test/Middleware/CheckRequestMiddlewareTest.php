<?php

namespace ApiV1Test\Middleware;

use ApiV1\Action\Code;
use ApiV1\Middleware\CheckRequestMiddleware;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Office\Entity\ApiKey;
use Office\Entity\Kkt;
use Office\Entity\Processing;
use Office\Entity\Shop;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Json\Json;

class CheckRequestMiddlewareTest extends TestCase
{
    public function testIncorrectToken(): void
    {
        $middleware = new CheckRequestMiddleware($this->prophesize(EntityManager::class)->reveal());
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getQueryParams')
            ->willReturn([]);
        $response = $middleware->process($request, $this->prophesize(RequestHandlerInterface::class)->reveal());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $json = Json::decode($response->getBody()->getContents(), Json::TYPE_ARRAY);
        $this->assertArrayHasKey('code', $json);
        $this->assertArrayHasKey('message', $json);
        $this->assertEquals($json['code'], Code::INCORRECT_TOKEN);
        $this->assertEquals($json['message'], Code::getMessage(Code::INCORRECT_TOKEN));
    }

    public function testOldToken(): void
    {
        $queryResult = $this->createMock(AbstractQuery::class);
        $query = $this->createMock(QueryBuilder::class);
        $query->method('where')->willReturn($query);
        $query->method('setParameter')->willReturn($query);
        $query->method('getQuery')->willReturn($queryResult);
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('createQueryBuilder')
            ->willReturn($query);
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->setMethods(['getRepository'])
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->method('getRepository')->willReturn($repository);


        $middleware = new CheckRequestMiddleware($entityManager);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getQueryParams')
            ->willReturn(['token' => 'test']);
        $response = $middleware->process($request, $this->prophesize(RequestHandlerInterface::class)->reveal());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $json = Json::decode($response->getBody()->getContents(), Json::TYPE_ARRAY);
        $this->assertArrayHasKey('code', $json);
        $this->assertArrayHasKey('message', $json);
        $this->assertEquals($json['code'], Code::INCORRECT_TOKEN);
        $this->assertEquals($json['message'], Code::getMessage(Code::USE_OLD_TOKEN));
    }

    public function testIncorrectShop(): void
    {
        $queryResult = $this->createMock(AbstractQuery::class);
        $queryResult->method('getOneOrNullResult')
            ->willReturn(new ApiKey());
        $query = $this->createMock(QueryBuilder::class);
        $query->method('where')->willReturn($query);
        $query->method('setParameter')->willReturn($query);
        $query->method('getQuery')->willReturn($queryResult);
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('createQueryBuilder')
            ->willReturn($query);
        $repository->method('findOneBy')
            ->willReturn(null);
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->setMethods(['getRepository'])
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->method('getRepository')->willReturn($repository);


        $middleware = new CheckRequestMiddleware($entityManager);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getQueryParams')
            ->willReturn(['token' => 'test']);
        $response = $middleware->process($request, $this->prophesize(RequestHandlerInterface::class)->reveal());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $json = Json::decode($response->getBody()->getContents(), Json::TYPE_ARRAY);
        $this->assertArrayHasKey('code', $json);
        $this->assertArrayHasKey('message', $json);
        $this->assertEquals($json['code'], Code::INCORRECT_SHOP);
        $this->assertEquals($json['message'], Code::getMessage(Code::INCORRECT_SHOP));
    }

    public function testIncorrectOperation(): void
    {
        $queryResult = $this->createMock(AbstractQuery::class);
        $queryResult->method('getOneOrNullResult')
            ->willReturn(new ApiKey());
        $query = $this->createMock(QueryBuilder::class);
        $query->method('where')->willReturn($query);
        $query->method('setParameter')->willReturn($query);
        $query->method('getQuery')->willReturn($queryResult);
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('createQueryBuilder')
            ->willReturn($query);
        $repository->method('findOneBy')
            ->willReturn(new Shop());
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->setMethods(['getRepository'])
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->method('getRepository')->willReturn($repository);


        $middleware = new CheckRequestMiddleware($entityManager);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getQueryParams')
            ->willReturn(['token' => 'test']);
        $request->method('getAttribute')
            ->willReturn('test'); //TODO нужнали проверка на NULL?
        $response = $middleware->process($request, $this->prophesize(RequestHandlerInterface::class)->reveal());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $json = Json::decode($response->getBody()->getContents(), Json::TYPE_ARRAY);
        $this->assertArrayHasKey('code', $json);
        $this->assertArrayHasKey('message', $json);
        $this->assertEquals($json['code'], Code::INCORRECT_OPERATION);
        $this->assertEquals($json['message'], Code::getMessage(Code::INCORRECT_OPERATION));
    }

    public function testNotFoundKkt(): void
    {
        $queryResult = $this->createMock(AbstractQuery::class);
        $queryResult->method('getOneOrNullResult')
            ->willReturn(new ApiKey());
        $query = $this->createMock(QueryBuilder::class);
        $query->method('where')->willReturn($query);
        $query->method('setParameter')->willReturn($query);
        $query->method('getQuery')->willReturn($queryResult);
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('createQueryBuilder')
            ->willReturn($query);
        $repository->method('findOneBy')
            ->willReturn(new Shop());
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->setMethods(['getRepository'])
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->method('getRepository')->willReturn($repository);


        $middleware = new CheckRequestMiddleware($entityManager);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getQueryParams')
            ->willReturn(['token' => 'test']);
        $request->method('getAttribute')
            ->willReturn(CheckRequestMiddleware::ALLOWED_OPERATION[Processing::OPERATION_SELL]); //TODO нужнали проверка на NULL?
        $response = $middleware->process($request, $this->prophesize(RequestHandlerInterface::class)->reveal());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $json = Json::decode($response->getBody()->getContents(), Json::TYPE_ARRAY);
        $this->assertArrayHasKey('code', $json);
        $this->assertArrayHasKey('message', $json);
        $this->assertEquals($json['code'], Code::NOT_FOUND_KKT);
        $this->assertEquals($json['message'], Code::getMessage(Code::NOT_FOUND_KKT));
    }

    public function testOk(): void
    {
        $queryResult = $this->createMock(AbstractQuery::class);
        $queryResult->method('getOneOrNullResult')
            ->willReturn(new ApiKey());
        $query = $this->createMock(QueryBuilder::class);
        $query->method('where')->willReturn($query);
        $query->method('setParameter')->willReturn($query);
        $query->method('getQuery')->willReturn($queryResult);
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('createQueryBuilder')
            ->willReturn($query);
        $repository->method('findOneBy')
            ->willReturn((new Shop())->addKkt((new Kkt())->setIsEnabled(true)->setIsFiscalized(true)));
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->setMethods(['getRepository'])
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->method('getRepository')->willReturn($repository);


        $middleware = new CheckRequestMiddleware($entityManager);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getQueryParams')
            ->willReturn(['token' => 'test']);
        $request->method('getAttribute')
            ->willReturn(CheckRequestMiddleware::ALLOWED_OPERATION[Processing::OPERATION_SELL]); //TODO нужнали проверка на NULL?
        $request->method('withAttribute')
            ->withAnyParameters()
            ->willReturn($request);

        $delegate = $this->createMock(RequestHandlerInterface::class);

        $response = $middleware->process($request, $delegate);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testGetActiveKkt(): void
    {
        /** @var CheckRequestMiddleware $action */
        $action = $this->getMockBuilder(CheckRequestMiddleware::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();

        $shop = new Shop();
        $this->assertNull($action->getActiveKkt($shop));

        $shop->addKkt((new Kkt())->setIsEnabled(true));
        $this->assertNull($action->getActiveKkt($shop));

        $shop->addKkt((new Kkt())->setIsEnabled(true)->setIsFiscalized(true));
        $this->assertInstanceOf(Kkt::class, $action->getActiveKkt($shop));
    }
}
