<?php

namespace ApiV1Test\Action;

use ApiV1\Action\Code;
use ApiV1\Action\TokenAction;
use ApiV1\Service\TokenService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Office\Entity\ApiKey;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

class TokenActionTest extends TestCase
{
    /** @var StreamInterface|ObjectProphecy */
    private $stream;

    /** @var ServerRequestInterface|ObjectProphecy */
    private $request;

    /** @var RequestHandlerInterface|ObjectProphecy */
    private $delegate;

    /** @var EntityManager|ObjectProphecy */
    private $entityManager;

    protected function setUp(): void
    {
        $this->stream = $this->prophesize(StreamInterface::class);
        $this->stream->getContents()->willReturn('{}');
        $this->request = $this->prophesize(ServerRequestInterface::class);
        $this->request->getBody()->willReturn($this->stream);
        $this->delegate = $this->prophesize(RequestHandlerInterface::class);
        $this->entityManager = $this->prophesize(EntityManager::class);
    }

    public function testProcessNotLogin()
    {
        /** @var TokenAction $tokenAction */
        $tokenAction = $this->tokenAction = $this->getMockBuilder(TokenAction::class)
            ->setMethods()
            ->disableOriginalConstructor()
            ->getMock();
        /** @var ResponseInterface $response */
        $response = $tokenAction->process($this->request->reveal(), $this->delegate->reveal());
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('token', $json);
        $this->assertArrayHasKey('code', $json);

        $this->assertEquals(Code::INCORRECT_LOGIN, $json['code']);
        $this->assertEquals(Code::getMessage(Code::INCORRECT_LOGIN), $json['message']);
        $this->assertNull($json['token']);
    }

    public function testNotApiKey()
    {
        $repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->setMethods(['findOneBy'])
            ->getMock();
        $this->entityManager->getRepository(Argument::any())
            ->willReturn($repository);

        $this->stream->getContents()->willReturn('{"login": "test", "password": "test"}');
        $this->request->getBody()->willReturn($this->stream);

        $tokenAction = new TokenAction(
            $this->entityManager->reveal(),
            $this->prophesize(TokenService::class)->reveal()
        );
        /** @var ResponseInterface $response */
        $response = $tokenAction->process($this->request->reveal(), $this->delegate->reveal());
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('token', $json);
        $this->assertArrayHasKey('code', $json);

        $this->assertEquals(Code::INCORRECT_LOGIN, $json['code']);
        $this->assertEquals(Code::getMessage(Code::INCORRECT_LOGIN), $json['message']);
        $this->assertNull($json['token']);
    }

    public function testReturnOldKey()
    {
        $repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->setMethods(['findOneBy'])
            ->getMock();
        $repository->method('findOneBy')->willReturn((new ApiKey())->setToken(uniqid(time())));
        $this->entityManager->getRepository(Argument::any())
            ->willReturn($repository);

        $this->stream->getContents()->willReturn('{"login": "test", "password": "test"}');
        $this->request->getBody()->willReturn($this->stream);

        /** @var TokenService|ObjectProphecy $tokenService */
        $tokenService = $this->prophesize(TokenService::class);
        $tokenService->checkToken(Argument::type(ApiKey::class))->willReturn(false);

        $tokenAction = new TokenAction(
            $this->entityManager->reveal(),
            $tokenService->reveal()
        );
        /** @var ResponseInterface $response */
        $response = $tokenAction->process($this->request->reveal(), $this->delegate->reveal());
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('token', $json);
        $this->assertArrayHasKey('code', $json);

        $this->assertEquals(Code::USE_OLD_TOKEN, $json['code']);
        $this->assertNull($json['message']);
        $this->assertNotNull($json['token']);
    }

    public function testReturnNewKey()
    {
        $repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->setMethods(['findOneBy'])
            ->getMock();
        $repository->method('findOneBy')->willReturn((new ApiKey())->setToken(uniqid(time())));
        $this->entityManager->getRepository(Argument::any())
            ->willReturn($repository);

        $this->stream->getContents()->willReturn('{"login": "test", "password": "test"}');
        $this->request->getBody()->willReturn($this->stream);

        /** @var TokenService|ObjectProphecy $tokenService */
        $tokenService = $this->prophesize(TokenService::class);
        $tokenService->checkToken(Argument::type(ApiKey::class))->willReturn(true);

        $tokenAction = new TokenAction(
            $this->entityManager->reveal(),
            $tokenService->reveal()
        );
        /** @var ResponseInterface $response */
        $response = $tokenAction->process($this->request->reveal(), $this->delegate->reveal());
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('token', $json);
        $this->assertArrayHasKey('code', $json);

        $this->assertEquals(Code::USE_NEW_TOKEN, $json['code']);
        $this->assertNull($json['message']);
        $this->assertNotNull($json['token']);
    }
}
