<?php

declare(strict_types=1);

namespace App;

use App\Shared\RateLimiter\CompilerPass\RateLimitingPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    #[\Override]
    public function buildContainer(): ContainerBuilder
    {
        $containerBuilder = parent::buildContainer();
        $containerBuilder->addCompilerPass(new RateLimitingPass());

        return $containerBuilder;
    }
}
