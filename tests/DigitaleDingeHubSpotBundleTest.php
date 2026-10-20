<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Tests;

use DigitaleDinge\HubSpotBundle\DigitaleDingeHubSpotBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DigitaleDingeHubSpotBundleTest extends TestCase
{
    public function testRegistersTheServices(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');
        $container->setParameter('kernel.build_dir', sys_get_temp_dir());

        new DigitaleDingeHubSpotBundle()->getContainerExtension()?->load([], $container);

        $services = array_filter(
            $container->getDefinitions(),
            static fn (string $id): bool => str_starts_with($id, 'digitale_dinge_hubspot.') || str_starts_with($id, 'DigitaleDinge\\'),
            ARRAY_FILTER_USE_KEY,
        );

        $this->assertCount(10, $services);

        foreach ($services as $id => $definition) {
            $this->assertTrue(class_exists($definition->getClass() ?? $id), $id);
        }
    }
}
