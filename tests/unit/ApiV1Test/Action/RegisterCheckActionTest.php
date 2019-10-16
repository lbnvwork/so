<?php

namespace ApiV1Test\Action;

use ApiV1\Action\Code;
use ApiV1\Action\RegisterCheckAction;
use ApiV1\Service\Check\Correction;
use ApiV1\Service\Check\Normal;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Office\Entity\Processing;
use Office\Entity\Shop;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Json\Json;

class RegisterCheckActionTest extends TestCase
{
    public function testProcessIncorrectJson()
    {
        /** @var RegisterCheckAction $action */
        $action = $this->getMockBuilder(RegisterCheckAction::class)
            ->setMethods()
            ->disableOriginalConstructor()
            ->getMock();
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')
            ->willReturn('{}');
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getBody')
            ->willReturn($stream);
        $delegate = $this->createMock(RequestHandlerInterface::class);

        $response = $action->process($request, $delegate);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $json = Json::decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('code', $json);
        $this->assertArrayHasKey('message', $json);
        $this->assertEquals(Code::INCORECT_DATA, $json['code']);
        $this->assertNotNull($json['message']);
    }

    public function testProcessExists()
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $repository->method('findOneBy')
            ->withAnyParameters()
            ->willReturn(new Processing());

        $entityManager->method('getRepository')
            ->withAnyParameters()
            ->willReturn($repository);

        $action = new RegisterCheckAction(
            $entityManager,
            $this->createMock(Normal::class),
            $this->createMock(Correction::class)
        );
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')
            ->willReturn('{"external_id": "1"}');
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getBody')
            ->willReturn($stream);
        $delegate = $this->createMock(RequestHandlerInterface::class);

        $response = $action->process($request, $delegate);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $json = Json::decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('status', $json);
        $this->assertEquals('accept', $json['status']);
    }

    public function testAcceptCheck()
    {
        $_data = [
            'external_id' => 1,
            'receipt'     => [
                'total' => 10,
                'items' => [
                    [
                        'name'     => 'test',
                        'price'    => 10,
                        'quantity' => 2,
                        'tax'      => 'none',
                    ],
                ],
            ],
            'service'     => [
                'callback_url' => 'test',
            ],
        ];

        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $repository->method('findOneBy')
            ->withAnyParameters()
            ->willReturn(null);

        $entityManager->method('getRepository')
            ->withAnyParameters()
            ->willReturn($repository);

        $normalCheck = $this->createMock(Normal::class);
        $normalCheck->method('accept')->willReturn(['id' => 1]);

        $action = new RegisterCheckAction(
            $entityManager,
            $normalCheck,
            $this->createMock(Correction::class)
        );
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')
            ->willReturn(Json::encode($_data));
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getBody')
            ->willReturn($stream);

        $requestMap = [
            [
                'shop',
                null,
                new Shop(),
            ],
            [
                'operation',
                null,
                Processing::OPERATION_SELL_CORRECTION,
            ],
        ];

        $request->method('getAttribute')
            ->willReturnMap($requestMap);

        $delegate = $this->createMock(RequestHandlerInterface::class);

        $response = $action->process($request, $delegate);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $json = Json::decode($response->getBody()->getContents(), Json::TYPE_ARRAY);
        $this->assertArrayHasKey('id', $json);
        $this->assertEquals(1, $json['id']);
    }
}
