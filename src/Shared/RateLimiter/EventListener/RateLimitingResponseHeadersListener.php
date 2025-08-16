<?php

declare(strict_types=1);

namespace App\Shared\RateLimiter\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimit;

#[AsEventListener(KernelEvents::RESPONSE)]
final readonly class RateLimitingResponseHeadersListener
{
    public function __invoke(ResponseEvent $event): void
    {
        if (($rateLimit = $event->getRequest()->attributes->get('rate_limit')) instanceof RateLimit) {
            $event->getResponse()->headers->add([
                'RateLimit-Remaining' => $rateLimit->getRemainingTokens(),
                'RateLimit-Reset' => time() - $rateLimit->getRetryAfter()->getTimestamp(),
                'RateLimit-Limit' => $rateLimit->getLimit(),
            ]);
        }
    }
}
