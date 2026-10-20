<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Tests\HubSpot;

use DigitaleDinge\HubSpotBundle\HubSpot\Endpoint;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EndpointTest extends TestCase
{
    #[DataProvider('provideEndpoints')]
    public function testReturnsTheUrlOfTheForm(Endpoint $endpoint, string $url): void
    {
        $this->assertSame($url, $endpoint->getUrl('12345', 'a1b2c3d4'));
    }

    public static function provideEndpoints(): iterable
    {
        yield 'submit' => [Endpoint::Submit, 'https://api.hsforms.com/submissions/v3/integration/submit/12345/a1b2c3d4'];
        yield 'render definition' => [Endpoint::RenderDefinition, 'https://forms.hsforms.com/embed/v4/render-definition/12345/a1b2c3d4'];
        yield 'embed definition' => [Endpoint::EmbedDefinition, 'https://forms.hsforms.com/embed/v3/form/12345/a1b2c3d4/json'];
    }
}
