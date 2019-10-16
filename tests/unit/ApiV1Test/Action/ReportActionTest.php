<?php

namespace ApiV1Test\Action;

use ApiV1\Action\Code;
use ApiV1\Action\ReportAction;
use ApiV1\Service\Check\Correction;
use ApiV1\Service\Check\Normal;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Office\Entity\Processing;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

class ReportActionTest extends TestCase
{
    public function testNotFound(): void
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn(null);
        $delegate = $this->createMock(RequestHandlerInterface::class);

        $action = new ReportAction(
            $entityManager,
            $this->prophesize(Normal::class)->reveal(),
            $this->prophesize(Correction::class)->reveal()
        );

        $response = $action->process($request, $delegate);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals(Code::INCORRECT_PROCESSING_ID, $json['code']);
        $this->assertEquals(Code::getMessage(Code::INCORRECT_PROCESSING_ID), $json['message']);
    }

    public function testAccept(): void
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn((new Processing())->setStatus(Processing::STATUS_ACCEPT));

        $delegate = $this->createMock(RequestHandlerInterface::class);

        $action = new ReportAction(
            $entityManager,
            $this->prophesize(Normal::class)->reveal(),
            $this->prophesize(Correction::class)->reveal()
        );

        $response = $action->process($request, $delegate);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $json = json_decode($response->getBody(), true);
        $this->assertNull($json['id']);
        $this->assertEquals('accept', $json['status']);
    }

    public function testPrepare(): void
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn((new Processing())->setStatus(Processing::STATUS_PREPARE));

        $delegate = $this->createMock(RequestHandlerInterface::class);

        $action = new ReportAction(
            $entityManager,
            $this->prophesize(Normal::class)->reveal(),
            $this->prophesize(Correction::class)->reveal()
        );

        $response = $action->process($request, $delegate);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $json = json_decode($response->getBody(), true);
        $this->assertNull($json['id']);
        $this->assertEquals('processing', $json['status']);
    }

    public function testErrorPrint(): void
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn((new Processing())->setError('error 1'));
        $delegate = $this->createMock(RequestHandlerInterface::class);


        $normal = $this->getMockBuilder(Normal::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();
        $action = new ReportAction(
            $entityManager,
            $normal,
            $this->prophesize(Correction::class)->reveal()
        );
        $response = $action->process($request, $delegate);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $json = json_decode($response->getBody(), true);
        $this->assertEquals('error', $json['status']);
        $this->assertEquals('error 1', $json['error']['message']);
        $this->assertEquals(Code::INCORECT_DATA, $json['error']['code']);
    }

    public function testSendOk(): void
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn((new Processing())->setStatus(Processing::STATUS_SEND_CLIENT));
        $delegate = $this->createMock(RequestHandlerInterface::class);

        /** @var Normal|ObjectProphecy $normal */
        $normal = $this->prophesize(Normal::class);
        $normal->report(Argument::any())->willReturn([]);

        $action = new ReportAction(
            $entityManager,
            $normal->reveal(),
            $this->prophesize(Correction::class)->reveal()
        );

        $response = $action->process($request, $delegate);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
}
