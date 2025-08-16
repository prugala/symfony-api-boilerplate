<?php

declare(strict_types=1);

namespace App\Tests\Shared\RateLimiter\EventListener;

use App\Shared\RateLimiter\EventListener\RateLimitExceededExceptionListener;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\RateLimiter\Exception\RateLimitExceededException;

final class RateLimitExceededExceptionListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testItCatchRateLimitExceededException(): void
    {
        $exception = $this->prophesize(RateLimitExceededException::class);

        $event = new ExceptionEvent($this->prophesize(HttpKernelInterface::class)->reveal(), $this->prophesize(Request::class)->reveal(), 1, $exception->reveal());

        new RateLimitExceededExceptionListener()->__invoke($event);

        $response = $event->getResponse();

        self::assertSame(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());
    }
}
