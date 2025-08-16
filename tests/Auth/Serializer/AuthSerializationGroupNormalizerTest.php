<?php

declare(strict_types=1);

namespace App\Tests\Auth\Serializer;

use App\Auth\Serializer\AuthSerializationGroupNormalizer;
use App\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AuthSerializationGroupNormalizerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<NormalizerInterface> */
    private ObjectProphecy $normalizer;

    /** @var ObjectProphecy<TokenStorageInterface> */
    private ObjectProphecy $tokenStorage;

    public function setUp(): void
    {
        $this->normalizer = $this->prophesize(NormalizerInterface::class);
        $this->tokenStorage = $this->prophesize(TokenStorageInterface::class);
    }

    public function testItNormalizesForUser(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER', 'ROLE_TEST']);
        $token = $this->prophesize(TokenInterface::class);
        $token->getUser()->willReturn($user);
        $token->getRoleNames()->willReturn(['ROLE_USER', 'ROLE_TEST']);
        $this->tokenStorage->getToken()->willReturn($token->reveal());
        $roleHierarchy = $this->prophesize(RoleHierarchyInterface::class);
        $roleHierarchy->getReachableRoleNames($user->getRoles())->willReturn(['ROLE_USER', 'ROLE_TEST']);

        $data = ['key' => 'value'];
        $context = ['groups' => ['test:get']];

        $this->normalizer->normalize($data, null, ['groups' => ['test:get', 'test:get:user', 'test:get:test']])->willReturn([])->shouldBeCalled();

        new AuthSerializationGroupNormalizer($this->normalizer->reveal(), $this->tokenStorage->reveal(), $roleHierarchy->reveal())->normalize($data, null, $context);
    }

    public function testItNormalizesForGuest(): void
    {
        $this->tokenStorage->getToken()->willReturn(null);
        $roleHierarchy = $this->prophesize(RoleHierarchyInterface::class);

        $data = ['key' => 'value'];
        $context = ['groups' => ['test:get']];

        $this->normalizer->normalize($data, null, ['groups' => ['test:get', 'test:get:guest']])->willReturn([])->shouldBeCalled();

        new AuthSerializationGroupNormalizer($this->normalizer->reveal(), $this->tokenStorage->reveal(), $roleHierarchy->reveal())->normalize($data, null, $context);
    }
}
