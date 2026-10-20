<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Tests\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use DigitaleDinge\HubSpotBundle\ContaoManager\Plugin;
use DigitaleDinge\HubSpotBundle\Controller\Backend\HubSpotImportController;
use DigitaleDinge\HubSpotBundle\DigitaleDingeHubSpotBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Loader\AttributeClassLoader;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Route;

final class PluginTest extends TestCase
{
    public function testReturnsTheBundleConfiguration(): void
    {
        $configs = new Plugin()->getBundles($this->createStub(ParserInterface::class));

        $this->assertCount(1, $configs);
        $this->assertSame(DigitaleDingeHubSpotBundle::class, $configs[0]->getName());
        $this->assertSame([ContaoCoreBundle::class], $configs[0]->getLoadAfter());
    }

    public function testLoadsTheImportRoute(): void
    {
        $classLoader = new class() extends AttributeClassLoader {
            /**
             * @param \ReflectionClass<object> $class
             */
            protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, object $attr): void
            {
                $route->setDefault('_controller', $class->getName());
            }
        };

        $locator = new FileLocator();
        $resolver = new LoaderResolver([new YamlFileLoader($locator), new AttributeDirectoryLoader($locator, $classLoader)]);

        $route = new Plugin()->getRouteCollection($resolver, $this->createStub(KernelInterface::class))?->get('dd_hubspot_import');

        $this->assertNotNull($route);
        $this->assertSame('/%contao.backend.route_prefix%/hubspot/import/{id}', $route->getPath());
        $this->assertSame(HubSpotImportController::class, $route->getDefault('_controller'));
        $this->assertSame('backend', $route->getDefault('_scope'));
        $this->assertSame(['GET', 'POST'], $route->getMethods());
    }
}
