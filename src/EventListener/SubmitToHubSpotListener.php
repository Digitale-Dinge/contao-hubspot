<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Form;
use DigitaleDinge\HubSpotBundle\HubSpot\HubSpotClient;
use DigitaleDinge\HubSpotBundle\HubSpot\SubmissionFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsHook('processFormData')]
readonly class SubmitToHubSpotListener
{
    public function __construct(
        private SubmissionFactory $submissionFactory,
        private HubSpotClient $client,
        private RequestStack $requestStack,
    ) {
    }

    /**
     * @param array<string, mixed>      $submittedData
     * @param array<string, mixed>      $formData
     * @param array<string, mixed>|null $files
     * @param array<string, string>     $labels
     */
    public function __invoke(array $submittedData, array $formData, array|null $files, array $labels, Form $form): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $portalId = $formData['dd_hubspot_portal_id'] ?? null;
        $formId = $formData['dd_hubspot_form_id'] ?? null;

        if (!$formData['dd_hubspot'] || !\is_string($portalId) || !\is_string($formId) || !$request instanceof Request) {
            return;
        }

        $submission = $this->submissionFactory->create(is_numeric($formData['id']) ? (int) $formData['id'] : 0, $submittedData, $request);

        $this->client->submit($portalId, $formId, $submission);
    }
}
