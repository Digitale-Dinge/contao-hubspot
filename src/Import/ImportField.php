<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Import;

use Contao\StringUtil;
use DigitaleDinge\HubSpotBundle\HubSpot\LegalBasis;
use DigitaleDinge\HubSpotBundle\HubSpot\LegalConsent;
use DigitaleDinge\HubSpotBundle\HubSpot\ObjectType;

readonly class ImportField
{
    /**
     * @param list<array{value: string, label: string}> $options
     */
    public function __construct(
        public string $type,
        public string $name = '',
        public string $label = '',
        public bool $mandatory = false,
        public array $options = [],
        public string $text = '',
        public string $value = '',
        public string $rgxp = '',
        public ObjectType|null $objectType = null,
        public LegalConsent|null $legalConsent = null,
        public int $subscriptionTypeId = 0,
        public LegalBasis $legalBasis = LegalBasis::Lead,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toRow(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'label' => StringUtil::specialchars($this->label),
            'mandatory' => (int) $this->mandatory,
            'options' => $this->options ? serialize(array_map($this->encodeOption(...), $this->options)) : null,
            'text' => $this->text ?: null,
            'value' => StringUtil::specialchars($this->value),
            'rgxp' => $this->rgxp,
            'slabel' => 'submit' === $this->type ? StringUtil::specialchars($this->label) : '',
            'dd_hubspot' => (int) ($this->objectType instanceof ObjectType || $this->legalConsent instanceof LegalConsent),
            'dd_hubspot_object_type' => ($this->objectType ?? ObjectType::Contact)->value,
            'dd_hubspot_legal' => $this->legalConsent->value ?? '',
            'dd_hubspot_legal_basis' => $this->legalBasis->value,
            'dd_hubspot_subscription_type_id' => $this->subscriptionTypeId,
        ];
    }

    /**
     * @param array{value: string, label: string} $option
     *
     * @return array{value: string, label: string}
     */
    private function encodeOption(array $option): array
    {
        return [
            'value' => StringUtil::specialchars($option['value']),
            'label' => StringUtil::specialchars($option['label']),
        ];
    }
}
