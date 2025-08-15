<?php

declare(strict_types=1);

namespace App\Tests\Auth\Serializer;

use App\Auth\Serializer\AuthSerializationGroupNormalizer;
use App\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AuthSerializationGroupNormalizerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<NormalizerInterface> */
    private ObjectProphecy $normalizer;

    /** @var ObjectProphecy<Security> */
    private ObjectProphecy $security;

    public function setUp(): void
    {
        $this->normalizer = $this->prophesize(NormalizerInterface::class);
        $this->security = $this->prophesize(Security::class);
    }

    public function testItNormalizesForUser(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER', 'ROLE_TEST']);

        $this->security->getUser()->willReturn($user);

        $data = ['key' => 'value'];
        $context = ['groups' => ['test:get']];

        $this->normalizer->normalize($data, null, ['groups' => ['test:get', 'test:get:user', 'test:get:test']])->willReturn([])->shouldBeCalled();

        new AuthSerializationGroupNormalizer($this->normalizer->reveal(), $this->security->reveal())->normalize($data, null, $context);
    }

    public function testItNormalizesForGuest(): void
    {
        $this->security->getUser()->willReturn(null);

        $data = ['key' => 'value'];
        $context = ['groups' => ['test:get']];

        $this->normalizer->normalize($data, null, ['groups' => ['test:get', 'test:get:guest']])->willReturn([])->shouldBeCalled();

        new AuthSerializationGroupNormalizer($this->normalizer->reveal(), $this->security->reveal())->normalize($data, null, $context);
    }
}
