<?php

declare(strict_types=1);

namespace App\Application;

use App\Application\DependencyInjection\ApplicatingExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Symfony bundle facade for the Applicating RC component.
 *
 * The component remains responsible for its own business surface.
 * The host application only enables this bundle and imports routes when needed.
 */
final class ApplicatingBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new ApplicatingExtension();
    }
}
