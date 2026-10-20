<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\HubSpot;

class Submission implements \JsonSerializable
{
    /**
     * @var list<array{objectTypeId: string, name: string, value: string}>
     */
    private array $fields = [];

    /**
     * @var array{consentToProcess?: bool, text?: string}
     */
    private array $processingConsent = [];

    /**
     * @var list<array{value: bool, subscriptionTypeId: int, text: string}>
     */
    private array $communicationConsents = [];

    /**
     * @var array{value?: true, subscriptionTypeId?: int, legalBasis?: string, text?: string}
     */
    private array $legitimateInterest = [];

    public function __construct(
        private readonly string $pageUri = '',
        private readonly string $hubSpotUserToken = '',
    ) {
    }

    /**
     * @param string|list<string> $value
     */
    public function addField(string $name, array|string $value, ObjectType $objectType = ObjectType::Contact): self
    {
        $this->fields[] = [
            'objectTypeId' => $objectType->value,
            'name' => $name,
            'value' => \is_array($value) ? implode(';', $value) : $value,
        ];

        return $this;
    }

    public function setProcessingConsent(bool $value, string $text): self
    {
        $this->processingConsent = [
            'consentToProcess' => $value,
            'text' => $text,
        ];

        return $this;
    }

    public function addCommunicationConsent(int $subscriptionTypeId, bool $value, string $text): self
    {
        $this->communicationConsents[] = [
            'value' => $value,
            'subscriptionTypeId' => $subscriptionTypeId,
            'text' => $text,
        ];

        return $this;
    }

    public function setLegitimateInterest(int $subscriptionTypeId, LegalBasis $legalBasis, string $text): self
    {
        $this->legitimateInterest = [
            'value' => true,
            'subscriptionTypeId' => $subscriptionTypeId,
            'legalBasis' => $legalBasis->value,
            'text' => $text,
        ];

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'fields' => $this->fields,
            ...array_filter([
                'legalConsentOptions' => array_filter([
                    'consent' => [
                        ...$this->processingConsent, ...array_filter([
                            'communications' => $this->communicationConsents,
                        ])],
                    'legitimateInterest' => $this->legitimateInterest,
                ]),
                'context' => array_filter([
                    'pageUri' => $this->pageUri,
                    'hutk' => $this->hubSpotUserToken,
                ]),
            ]),
        ];
    }
}
