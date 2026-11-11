<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\HubSpot;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @experimental
 */
readonly class FormListLoader
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string|null $accessToken,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function load(): array
    {
        if (!$this->accessToken) {
            return [];
        }

        $forms = [];
        $query = [
            'limit' => 100,
            'archived' => 'false',
        ];

        try {
            do {
                $data = $this->httpClient->request(
                    'GET',
                    Endpoint::Forms->value,
                    [
                        'auth_bearer' => $this->accessToken,
                        'query' => $query,
                    ],
                )->toArray();

                foreach ((array) ($data['results'] ?? []) as $form) {
                    if (\is_array($form) && \is_string($form['id'] ?? null) && \is_string($form['name'] ?? null)) {
                        $forms[$form['id']] = $form['name'];
                    }
                }

                $after = $this->getNextPage($data);
                $query['after'] = $after;
            }
            while (null !== $after);
        }
        catch (ExceptionInterface $e) {
            $this->logger->error(\sprintf('Loading the HubSpot forms failed: %s', $e->getMessage()));

            return [];
        }

        natcasesort($forms);

        return $forms;
    }

    /**
     * @param array<mixed> $data
     */
    private function getNextPage(array $data): string|null
    {
        $paging = $data['paging'] ?? null;
        $next = \is_array($paging) ? $paging['next'] ?? null : null;
        $after = \is_array($next) ? $next['after'] ?? null : null;

        return \is_string($after) && '' !== $after ? $after : null;
    }
}
