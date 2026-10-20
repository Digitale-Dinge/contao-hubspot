<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\HubSpot;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class HubSpotClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
    ) {
    }

    public function submit(string $portalId, string $formId, Submission $submission): void
    {
        try {
            $response = $this->httpClient->request(
                'POST',
                Endpoint::Submit->getUrl($portalId, $formId),
                [
                    'json' => $submission,
                ],
            );

            if (200 !== $response->getStatusCode()) {
                $this->logger->error(\sprintf('HubSpot rejected the submission to form "%s" (HTTP %s): %s', $formId, $response->getStatusCode(), $response->getContent(false)));
            }
        }
        catch (ExceptionInterface $e) {
            $this->logger->error(\sprintf('The submission to HubSpot form "%s" failed: %s', $formId, $e->getMessage()));
        }
    }
}
