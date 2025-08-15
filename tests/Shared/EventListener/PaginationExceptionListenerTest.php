<?php

declare(strict_types=1);

namespace App\Tests\Shared\EventListener;

use App\Shared\EventListener\PaginationExceptionListener;
use Pagerfanta\Exception\NotValidMaxPerPageException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class PaginationExceptionListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testItCatchPaginationException(): void
    {
        $exception = new NotValidMaxPerPageException('test');
        $event = new ExceptionEvent($this->prophesize(HttpKernelInterface::class)->reveal(), $this->prophesize(Request::class)->reveal(), 1, $exception);

        new PaginationExceptionListener()->__invoke($event);

        $response = $event->getResponse();
        $content = json_decode($response->getContent(), true);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertArrayHasKey('violations', $content);
        self::assertSame([['field' => 'limit', 'message' => 'test']], $content['violations']);
    }
}
