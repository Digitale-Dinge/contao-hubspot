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
        private string|null $accessToken = null,
    ) {
    }

    public function load(string $portalId, string $formId): FormDefinition|null
    {
        return $this->loadFormDefinition($portalId, $formId)
            ?? $this->loadRenderDefinition($portalId, $formId)
            ?? $this->loadEmbedDefinition($portalId, $formId);
    }

    /**
     * @experimental
     */
    private function loadFormDefinition(string $portalId, string $formId): FormDefinition|null
    {
        if (!$this->accessToken) {
            return null;
        }

        try {
            $definition = $this->fetch(
                Endpoint::Form->getUrl($portalId, $formId),
                [
                    'auth_bearer' => $this->accessToken,
                ],
            );

            return $definition ? $this->parser->parseFormDefinition($definition) : null;
        }
        catch (\JsonException|\TypeError|\ValueError) {
            return null;
        }
    }

    /**
     * @internal
     *
     * @experimental
     */
    private function loadRenderDefinition(string $portalId, string $formId): FormDefinition|null
    {
        try {
            $definition = $this->fetch(Endpoint::RenderDefinition->getUrl($portalId, $formId));

            return $definition ? $this->parser->parseRenderDefinition($definition) : null;
        }
        catch (\TypeError|\ValueError) {
            return null;
        }
    }

    /**
     * @internal
     *
     * @experimental
     */
    private function loadEmbedDefinition(string $portalId, string $formId): FormDefinition|null
    {
        try {
            $definition = $this->fetch(Endpoint::EmbedDefinition->getUrl($portalId, $formId));

            return $definition ? $this->parser->parseEmbedDefinition($definition) : null;
        }
        catch (\JsonException|\TypeError|\ValueError) {
            return null;
        }
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<mixed>|null
     */
    private function fetch(string $url, array $options = []): array|null
    {
        try {
            return $this->httpClient->request('GET', $url, $options)->toArray();
        }
        catch (ExceptionInterface) {
            return null;
        }
    }
}
