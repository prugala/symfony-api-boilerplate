<?php

declare(strict_types=1);

namespace App\Tests\Shared\EventListener;

use App\Shared\EventListener\ApiResponseListener;
use App\Shared\Model\ApiErrorResponse;
use App\Shared\Model\ApiResponse;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

final class ApiResponseListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testItResponsesForItem(): void
    {
        $object = new \stdClass();
        $object->name = 'Peter';

        $serializer = $this->prophesize(SerializerInterface::class);
        $serializer->serialize(['data' => $object], 'json', Argument::any())->willReturn('{"data":{"name":"Peter"}}');

        $result = new ApiResponse($object);
        $event = new ViewEvent($this->prophesize(HttpKernelInterface::class)->reveal(), $this->prophesize(Request::class)->reveal(), 1, $result);

        new ApiResponseListener($serializer->reveal())->__invoke($event);

        $response = $event->getResponse();

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('{"data":{"name":"Peter"}}', $response->getContent());
    }

    public function testItResponsesForList(): void
    {
        $data = [$this->prophesize(\stdClass::class)->reveal()];

        $pagerFanta = $this->prophesize(Pagerfanta::class);
        $pagerFanta->getCurrentPageResults()->willReturn($data);
        $pagerFanta->getNbResults()->willReturn(1);
        $pagerFanta->hasNextPage()->willReturn(true);
        $pagerFanta->hasPreviousPage()->willReturn(false);

        $serializer = $this->prophesize(SerializerInterface::class);
        $serializer->serialize(
            [
                'data' => $data,
                'total' => 1,
                'has_next_page' => true,
                'has_previous_page' => false,
            ],
            'json',
            Argument::any()
        )->willReturn('{"data":[{}],"total":1,"has_next_page":true}');

        $result = new ApiResponse($pagerFanta->reveal());
        $event = new ViewEvent($this->prophesize(HttpKernelInterface::class)->reveal(), $this->prophesize(Request::class)->reveal(), 1, $result);

        new ApiResponseListener($serializer->reveal())->__invoke($event);

        $response = $event->getResponse();

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('{"data":[{}],"total":1,"has_next_page":true}', $response->getContent());
    }

    public function testItResponsesForError(): void
    {
        $result = new ApiErrorResponse('test error');
        $errorCode = Uuid::v5(Uuid::fromString(Uuid::NAMESPACE_DNS), $result->message)->toString();

        $serializer = $this->prophesize(SerializerInterface::class);
        $serializer->serialize(['message' => 'test error', 'code' => $errorCode], 'json', Argument::any())->willReturn('{"message":"test error","code":"'.$errorCode.'"}');

        $event = new ViewEvent($this->prophesize(HttpKernelInterface::class)->reveal(), $this->prophesize(Request::class)->reveal(), 1, $result);

        new ApiResponseListener($serializer->reveal())->__invoke($event);

        $response = $event->getResponse();

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertSame('{"message":"test error","code":"'.$errorCode.'"}', $response->getContent());
    }

    public function testItResponsesForErrorWithCodeAndStatusCode(): void
    {
        $result = new ApiErrorResponse('test error', 'test', Response::HTTP_INTERNAL_SERVER_ERROR);

        $serializer = $this->prophesize(SerializerInterface::class);
        $serializer->serialize(['message' => 'test error', 'code' => 'test'], 'json', Argument::any())->willReturn('{"message":"test error","code":"test"}');

        $event = new ViewEvent($this->prophesize(HttpKernelInterface::class)->reveal(), $this->prophesize(Request::class)->reveal(), 1, $result);

        new ApiResponseListener($serializer->reveal())->__invoke($event);

        $response = $event->getResponse();

        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        self::assertSame('{"message":"test error","code":"test"}', $response->getContent());
    }
}
