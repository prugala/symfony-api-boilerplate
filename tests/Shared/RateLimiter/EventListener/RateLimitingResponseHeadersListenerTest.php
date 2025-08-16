<?php

declare(strict_types=1);

namespace App\Tests\Shared\RateLimiter\EventListener;

use App\Shared\RateLimiter\EventListener\RateLimitingResponseHeadersListener;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\RateLimiter\RateLimit;

final class RateLimitingResponseHeadersListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testItAddsRateLimitHeaders(): void
    {
        $rateLimit = $this->prophesize(RateLimit::class);
        $rateLimit->getRemainingTokens()->willReturn(10);
        $rateLimit->getRetryAfter()->willReturn(new \DateTimeImmutable());
        $rateLimit->getLimit()->willReturn(15);

        $parameters = $this->prophesize(ParameterBag::class);
        $parameters->get('rate_limit')->willReturn(
            $rateLimit->reveal()
        );

        $request = $this->prophesize(Request::class);
        $request->attributes = $parameters;
        $kernel = $this->prophesize(HttpKernelInterface::class);
        $event = new ResponseEvent($kernel->reveal(), $request->reveal(), 1, new Response());

        new RateLimitingResponseHeadersListener()->__invoke($event);

        $response = $event->getResponse();

        $headerKeys = array_keys($response->headers->all());

        self::assertContains('ratelimit-remaining', $headerKeys);
        self::assertContains('ratelimit-reset', $headerKeys);
        self::assertContains('ratelimit-limit', $headerKeys);
        self::assertSame('10', $response->headers->get('ratelimit-remaining'));
        self::assertSame('0', $response->headers->get('ratelimit-reset'));
        self::assertSame('15', $response->headers->get('ratelimit-limit'));
    }
}
