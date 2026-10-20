<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Import;

use DigitaleDinge\HubSpotBundle\HubSpot\Endpoint;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class FormDefinitionLoader
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private FormDefinitionParser $parser,
    ) {
    }

    public function load(string $portalId, string $formId): FormDefinition|null
    {
        try {
            if ($definition = $this->fetch(Endpoint::RenderDefinition->getUrl($portalId, $formId))) {
                return $this->parser->parseRenderDefinition($definition);
            }

            if ($definition = $this->fetch(Endpoint::EmbedDefinition->getUrl($portalId, $formId))) {
                return $this->parser->parseEmbedDefinition($definition);
            }
        }
        catch (\JsonException|\TypeError|\ValueError) {
            return null;
        }

        return null;
    }

    /**
     * @return array<mixed>|null
     */
    private function fetch(string $url): array|null
    {
        try {
            return $this->httpClient->request('GET', $url)->toArray();
        }
        catch (ExceptionInterface) {
            return null;
        }
    }
}
