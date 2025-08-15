<?php

declare(strict_types=1);

namespace App\Tests\Shared\EventListener;

use App\Shared\EventListener\ValidationExceptionListener;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ValidationExceptionListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testItCatchValidationException(): void
    {
        $constraintViolationList = new ConstraintViolationList();
        $constraintViolationList->add(new ConstraintViolation('test', null, [], null, 'foo', 'foo', null, null, null, 'Foo error occurred'));
        $validationFailedException = new ValidationFailedException('test', $constraintViolationList);

        $exception = new UnprocessableEntityHttpException(previous: $validationFailedException);
        $event = new ExceptionEvent($this->prophesize(HttpKernelInterface::class)->reveal(), $this->prophesize(Request::class)->reveal(), 1, $exception);

        new ValidationExceptionListener()->__invoke($event);

        $response = $event->getResponse();
        $content = json_decode($response->getContent(), true);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        self::assertArrayHasKey('violations', $content);
        self::assertSame([['field' => 'foo', 'message' => 'test']], $content['violations']);
    }
}
