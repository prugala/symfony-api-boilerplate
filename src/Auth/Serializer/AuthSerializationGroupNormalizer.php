<?php

declare(strict_types=1);

namespace App\Auth\Serializer;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AutoconfigureTag('serializer.normalizer', ['priority' => -1000])]
final readonly class AuthSerializationGroupNormalizer implements NormalizerInterface
{
    private const string ROLE_PREFIX = 'ROLE_';
    private const string GUEST_ROLE = 'guest';

    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer,
        private Security $security,
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
        $user = $this->security->getUser();

        if (null === $user) {
            return [self::GUEST_ROLE];
        }

        $roles = [];

        foreach ($user->getRoles() as $role) {
            $roles[] = strtolower(substr($role, strlen(self::ROLE_PREFIX)));
        }

        return $roles;
    }
}
