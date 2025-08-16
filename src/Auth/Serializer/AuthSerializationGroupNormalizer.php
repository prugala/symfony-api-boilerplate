<?php

declare(strict_types=1);

namespace App\Auth\Serializer;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AutoconfigureTag('serializer.normalizer', ['priority' => -1000])]
final readonly class AuthSerializationGroupNormalizer implements NormalizerInterface
{
    private const string ROLE_PREFIX = 'ROLE_';
    private const string GUEST_ROLE = 'guest';

    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer,
        private TokenStorageInterface $tokenStorage,
        private RoleHierarchyInterface $roleHierarchy,
    ) {
    }

    /** @param array<string, mixed> $context */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->normalizer->supportsNormalization($data, $format, $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        foreach ($context['groups'] ?? [] as $group) {
            foreach ($this->getRoles() as $role) {
                $context['groups'][] = $group.':'.$role;
            }
        }

        /** @var array<string, mixed> $data */
        $data = $this->normalizer->normalize($data, $format, $context);

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => true,
        ];
    }

    /** @return array<string> */
    private function getRoles(): array
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        if (null === $user) {
            return [self::GUEST_ROLE];
        }

        $roles = [];

        foreach ($this->roleHierarchy->getReachableRoleNames($token->getRoleNames()) as $role) {
            $roles[] = strtolower(substr($role, strlen(self::ROLE_PREFIX)));
        }

        return $roles;
    }
}
