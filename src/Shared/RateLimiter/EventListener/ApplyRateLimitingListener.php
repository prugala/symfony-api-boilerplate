<?php

declare(strict_types=1);

namespace App\Shared\RateLimiter\EventListener;

use App\Auth\Enum\Role;
use App\User\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

#[AsEventListener(event: KernelEvents::CONTROLLER)]
final readonly class ApplyRateLimitingListener
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        #[Autowire('%rate_limiter.enabled%')]
        private bool $isRateLimiterEnabled,
        /** @var RateLimiterFactory[] */
        private array $rateLimiterClassMap,
        private RoleHierarchyInterface $roleHierarchy,
    ) {
    }

    public function __invoke(KernelEvent $event): void
    {
        if (!$this->isRateLimiterEnabled || !$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        /** @var string $controllerClass */
        $controllerClass = $request->attributes->get('_controller');

        $rateLimiter = $this->rateLimiterClassMap[$controllerClass] ?? null;

        if (null === $rateLimiter) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if ($token instanceof TokenInterface && in_array(Role::ADMIN->value, $this->roleHierarchy->getReachableRoleNames($token->getRoleNames()))) {
            return;
        }

        /** @var string $clientIp */
        $clientIp = $request->getClientIp();

        $this->ensureRateLimiting($request, $rateLimiter, $clientIp);
    }

    private function ensureRateLimiting(Request $request, RateLimiterFactory $rateLimiter, string $clientIp): void
    {
        $limit = $rateLimiter->create(sprintf('rate_limit_ip_%s', $clientIp))->consume();
        $request->attributes->set('rate_limit', $limit);
        $limit->ensureAccepted();

        $user = $this->tokenStorage->getToken()?->getUser();

        if ($user instanceof User) {
            $limit = $rateLimiter->create(sprintf('rate_limit_user_%s', $user->getId()))->consume();
            $request->attributes->set('rate_limit', $limit);
            $limit->ensureAccepted();
        }
    }
}
