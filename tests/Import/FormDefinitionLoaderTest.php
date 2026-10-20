<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Tests\Import;

use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\CoreBundle\String\HtmlDecoder;
use DigitaleDinge\HubSpotBundle\Import\FormDefinitionLoader;
use DigitaleDinge\HubSpotBundle\Import\FormDefinitionParser;
use DigitaleDinge\HubSpotBundle\Import\ImportField;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class FormDefinitionLoaderTest extends TestCase
{
    public function testLoadsTheRenderDefinition(): void
    {
        $response = new MockResponse(json_encode([
            'form' => [
                'modules' => [[
                    'type' => 'submitButton',
                    'text' => 'Send',
                ]],
            ],
        ], JSON_THROW_ON_ERROR));

        $definition = $this->createLoader([$response])->load('12345', 'a1b2c3d4');

        $this->assertSame('https://forms.hsforms.com/embed/v4/render-definition/12345/a1b2c3d4', $response->getRequestUrl());
        $this->assertEquals([new ImportField('submit', label: 'Send')], $definition?->fields);
    }

    public function testFallsBackToTheEmbedDefinitionForOlderForms(): void
    {
        $responses = [
            new MockResponse(
                '{"status":"error","message":"Form not found"}',
                [
                    'http_code' => 404,
                ],
            ),
            new MockResponse(json_encode([
                'form' => [
                    'submitText' => 'Send',
                    'formFieldGroups' => [],
                    'metaData' => [],
                ],
            ], JSON_THROW_ON_ERROR)),
        ];

        $definition = $this->createLoader($responses)->load('12345', 'a1b2c3d4');

        $this->assertSame('https://forms.hsforms.com/embed/v3/form/12345/a1b2c3d4/json', $responses[1]->getRequestUrl());
        $this->assertEquals([new ImportField('submit', label: 'Send')], $definition?->fields);
    }

    public function testReturnsNullIfTheFormCannotBeLoaded(): void
    {
        $responses = [
            new MockResponse('{"status":"error"}', [
                'http_code' => 404,
            ]),
            new MockResponse('{"status":"error"}', [
                'http_code' => 403,
            ]),
        ];

        $this->assertNull($this->createLoader($responses)->load('12345', 'a1b2c3d4'));
    }

    public function testReturnsNullIfTheValuesHaveTheWrongType(): void
    {
        $response = new MockResponse(json_encode([
            'form' => [
                'modules' => [[
                    'type' => 'submitButton',
                    'text' => ['Send'],
                ]],
            ],
        ], JSON_THROW_ON_ERROR));

        $this->assertNull($this->createLoader([$response])->load('12345', 'a1b2c3d4'));
    }

    public function testReturnsNullIfTheConsentOptionsAreInvalid(): void
    {
        $responses = [
            new MockResponse('{"status":"error"}', [
                'http_code' => 404,
            ]),
            new MockResponse(json_encode([
                'form' => [
                    'submitText' => 'Send',
                    'formFieldGroups' => [],
                    'metaData' => [[
                        'name' => 'legalConsentOptions',
                        'value' => '{invalid',
                    ]],
                ],
            ], JSON_THROW_ON_ERROR)),
        ];

        $this->assertNull($this->createLoader($responses)->load('12345', 'a1b2c3d4'));
    }

    /**
     * @param list<MockResponse> $responses
     */
    private function createLoader(array $responses): FormDefinitionLoader
    {
        return new FormDefinitionLoader(
            new MockHttpClient($responses),
            new FormDefinitionParser(new HtmlDecoder($this->createStub(InsertTagParser::class))),
        );
    }
}
