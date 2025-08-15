<?php

declare(strict_types=1);

namespace App\Tests\Shared\EventListener;

use App\Shared\EventListener\ExceptionListener;
use App\Shared\Exception\AbstractApiException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ExceptionListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testItCatchCustomException(): void
    {
        $exception = FooException::create('Foo error occurred');

        $event = new ExceptionEvent($this->prophesize(HttpKernelInterface::class)->reveal(), $this->prophesize(Request::class)->reveal(), 1, $exception);

        new ExceptionListener(new NullLogger())->__invoke($event);

        $response = $event->getResponse();

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertTrue(str_contains($response->getContent(), 'Foo error occurred'));
    }

    public function testItCatchGenericException(): void
    {
        $exception = new \Exception('test');

        $event = new ExceptionEvent($this->prophesize(HttpKernelInterface::class)->reveal(), $this->prophesize(Request::class)->reveal(), 1, $exception);

        new ExceptionListener(new NullLogger())->__invoke($event);

        $response = $event->getResponse();

        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        self::assertTrue(str_contains($response->getContent(), 'test'));
    }
}

class FooException extends AbstractApiException
{
}
