<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Import;

use Contao\CoreBundle\String\HtmlDecoder;
use Contao\StringUtil;
use DigitaleDinge\HubSpotBundle\HubSpot\LegalBasis;
use DigitaleDinge\HubSpotBundle\HubSpot\LegalConsent;
use DigitaleDinge\HubSpotBundle\HubSpot\ObjectType;

/**
 * @phpstan-type Option array{value: string, label: string}
 * @phpstan-type DataPrivacy array{
 *     isLegitimateInterest?: bool,
 *     legitimateInterestSubscriptionTypes?: list<int>,
 *     legitimateInterestLegalBasis?: string,
 *     communicationConsentText?: string,
 *     communicationConsentCheckboxes?: list<array{communicationTypeId: int, required: bool, label: string}>,
 *     processingConsentType?: string,
 *     processingConsentText?: string,
 *     processingConsentCheckboxLabel?: string,
 *     privacyPolicyText?: string,
 * }
 * @phpstan-type RenderModule array{
 *     type: string,
 *     modules?: list<array<string, mixed>>,
 *     level?: int,
 *     text?: string,
 *     html?: string,
 *     propertyReference?: string,
 *     label?: string,
 *     required?: bool,
 *     options?: list<Option>,
 * }
 * @phpstan-type RenderDefinition array{form: array{modules: list<array<string, mixed>>}}
 * @phpstan-type EmbedField array{
 *     name: string,
 *     label: string,
 *     required: bool,
 *     hidden: bool,
 *     fieldType: string,
 *     objectTypeId: string,
 *     defaultValue: string,
 *     options: list<Option>,
 * }
 * @phpstan-type EmbedGroup array{richText?: array{content?: string}, fields: list<EmbedField>}
 * @phpstan-type EmbedDefinition array{
 *     form: array{
 *         formFieldGroups: list<EmbedGroup>,
 *         metaData: list<array{name: string, value: string}>,
 *         submitText: string,
 *     },
 * }
 */
class FormDefinitionParser
{
    private const array DATA_PRIVACY_TEXTS = ['communicationConsentText', 'processingConsentText', 'privacyPolicyText'];

    /**
     * @var list<ImportField>
     */
    private array $fields = [];

    /**
     * @var list<string>
     */
    private array $unsupportedTypes = [];

    public function __construct(private readonly HtmlDecoder $htmlDecoder)
    {
    }

    /**
     * @param array<mixed> $definition
     */
    public function parseRenderDefinition(array $definition): FormDefinition
    {
        /** @var RenderDefinition $definition */
        $this->reset();
        $this->addModules($definition['form']['modules']);

        return $this->createFormDefinition();
    }

    /**
     * @param array<mixed> $definition
     */
    public function parseEmbedDefinition(array $definition): FormDefinition
    {
        /** @var EmbedDefinition $definition */
        $this->reset();

        foreach ($definition['form']['formFieldGroups'] as $group) {
            $this->addEmbedGroup($group);
        }

        foreach ($definition['form']['metaData'] as $metaData) {
            if ('legalConsentOptions' === $metaData['name']) {
                /** @var DataPrivacy $dataPrivacy */
                $dataPrivacy = json_decode($metaData['value'], true, flags: JSON_THROW_ON_ERROR);

                $this->addDataPrivacy($dataPrivacy);
            }
        }

        $this->addSubmit($definition['form']['submitText']);

        return $this->createFormDefinition();
    }

    private function reset(): void
    {
        $this->fields = [];
        $this->unsupportedTypes = [];
    }

    private function createFormDefinition(): FormDefinition
    {
        return new FormDefinition($this->fields, array_values(array_unique($this->unsupportedTypes)));
    }

    /**
     * @param list<array<string, mixed>> $modules
     */
    private function addModules(array $modules): void
    {
        /** @var list<RenderModule> $modules */
        foreach ($modules as $module) {
            $this->addModule($module);
        }
    }

    /**
     * @param RenderModule $module
     */
    private function addModule(array $module): void
    {
        match ($module['type']) {
            'step', 'row', 'navigationRow' => $this->addModules($module['modules'] ?? []),
            'heading' => $this->addExplanation(\sprintf('<h%1$d>%2$s</h%1$d>', $module['level'] ?? 2, StringUtil::specialchars($module['text'] ?? ''))),
            'richText' => $this->addExplanation($module['html'] ?? ''),
            'text', 'phone' => $this->addProperty('text', $module),
            'email' => $this->addProperty('text', $module, 'email'),
            'number' => $this->addProperty('text', $module, 'digit'),
            'multiLineText' => $this->addProperty('textarea', $module),
            'dropdownSelect' => $this->addProperty('select', $module),
            'radioSelect' => $this->addProperty('radio', $module),
            'multipleCheckbox' => $this->addProperty('checkbox', $module),
            'singleCheckbox' => $this->addSingleCheckbox($module),
            'dataPrivacy' => $this->addDataPrivacy($this->encodePlainTexts($module)),
            'submitButton' => $this->addSubmit($module['text'] ?? ''),
            default => $this->addUnsupportedType($module['type']),
        };
    }

    /**
     * @param RenderModule $module
     */
    private function addProperty(string $type, array $module, string $rgxp = ''): void
    {
        $propertyReference = $module['propertyReference'] ?? '';
        [$objectTypeId, $name] = explode('/', $propertyReference, 2) + ['', ''];
        $objectType = ObjectType::tryFrom($objectTypeId);

        if ('' === $name || !$objectType instanceof ObjectType) {
            $this->addUnsupportedType($propertyReference ?: $module['type']);

            return;
        }

        $this->fields[] = new ImportField(
            $type,
            name: $name,
            label: $module['label'] ?? '',
            mandatory: $module['required'] ?? false,
            options: $this->getOptions($module['options'] ?? []),
            rgxp: $rgxp,
            objectType: $objectType,
        );
    }

    /**
     * @param RenderModule $module
     */
    private function addSingleCheckbox(array $module): void
    {
        $module['options'] = [[
            'value' => 'true',
            'label' => $module['label'] ?? '',
        ]];

        $this->addProperty('checkbox', $module);
    }

    /**
     * @param EmbedGroup $group
     */
    private function addEmbedGroup(array $group): void
    {
        $this->addExplanation($group['richText']['content'] ?? '');

        foreach ($group['fields'] as $field) {
            $this->addEmbedField($field);
        }
    }

    /**
     * @param EmbedField $field
     */
    private function addEmbedField(array $field): void
    {
        $objectType = ObjectType::tryFrom($field['objectTypeId']);

        if (!$objectType instanceof ObjectType) {
            $this->addUnsupportedType($field['objectTypeId'].'/'.$field['name']);

            return;
        }

        if ($field['hidden']) {
            $this->fields[] = new ImportField('hidden', name: $field['name'], value: $field['defaultValue'], objectType: $objectType);

            return;
        }

        $type = $this->getEmbedFieldType($field['fieldType']);

        if (null === $type) {
            $this->addUnsupportedType($field['fieldType']);

            return;
        }

        $options = 'booleancheckbox' === $field['fieldType'] ? [[
            'value' => 'true',
            'label' => $field['label'],
        ]] : $field['options'];

        $this->fields[] = new ImportField(
            $type,
            name: $field['name'],
            label: $field['label'],
            mandatory: $field['required'],
            options: $this->getOptions($options),
            rgxp: $this->getEmbedFieldRgxp($field['fieldType'], $field['name']),
            objectType: $objectType,
        );
    }

    private function getEmbedFieldType(string $fieldType): string|null
    {
        return match ($fieldType) {
            'checkbox', 'booleancheckbox' => 'checkbox',
            'radio' => 'radio',
            'select' => 'select',
            'text', 'phonenumber', 'number' => 'text',
            'textarea' => 'textarea',
            default => null,
        };
    }

    private function getEmbedFieldRgxp(string $fieldType, string $name): string
    {
        return match (true) {
            'number' === $fieldType => 'digit',
            'email' === $name => 'email',
            default => '',
        };
    }

    /**
     * @param DataPrivacy $dataPrivacy
     */
    private function addDataPrivacy(array $dataPrivacy): void
    {
        if ($dataPrivacy['isLegitimateInterest'] ?? false) {
            $this->fields[] = new ImportField(
                'explanation',
                text: $dataPrivacy['privacyPolicyText'] ?? '',
                legalConsent: LegalConsent::LegitimateInterest,
                subscriptionTypeId: $dataPrivacy['legitimateInterestSubscriptionTypes'][0] ?? 0,
                legalBasis: str_contains($dataPrivacy['legitimateInterestLegalBasis'] ?? '', 'CLIENT') ? LegalBasis::Customer : LegalBasis::Lead,
            );

            return;
        }

        $this->addExplanation($dataPrivacy['communicationConsentText'] ?? '');

        foreach ($dataPrivacy['communicationConsentCheckboxes'] ?? [] as $checkbox) {
            $this->fields[] = new ImportField(
                'checkbox',
                name: 'subscription_type_'.$checkbox['communicationTypeId'],
                mandatory: $checkbox['required'],
                options: [[
                    'value' => 'true',
                    'label' => $this->toPlainText($checkbox['label']),
                ]],
                legalConsent: LegalConsent::Communication,
                subscriptionTypeId: $checkbox['communicationTypeId'],
            );
        }

        if ('REQUIRED_CHECKBOX' === ($dataPrivacy['processingConsentType'] ?? '')) {
            $this->addExplanation($dataPrivacy['processingConsentText'] ?? '');

            $this->fields[] = new ImportField(
                'checkbox',
                name: 'processing_consent',
                mandatory: true,
                options: [[
                    'value' => 'true',
                    'label' => $this->toPlainText($dataPrivacy['processingConsentCheckboxLabel'] ?? ''),
                ]],
                legalConsent: LegalConsent::Processing,
            );
        }
        else {
            $this->fields[] = new ImportField('explanation', text: $dataPrivacy['processingConsentText'] ?? '', legalConsent: LegalConsent::Processing);
        }

        $this->addExplanation($dataPrivacy['privacyPolicyText'] ?? '');
    }

    /**
     * @param array<string, mixed> $module
     *
     * @return DataPrivacy
     */
    private function encodePlainTexts(array $module): array
    {
        /** @var DataPrivacy $dataPrivacy */
        $dataPrivacy = $module;

        foreach (self::DATA_PRIVACY_TEXTS as $key) {
            if (isset($dataPrivacy[$key])) {
                $dataPrivacy[$key] = '<p>'.StringUtil::specialchars($dataPrivacy[$key]).'</p>';
            }
        }

        return $dataPrivacy;
    }

    private function addExplanation(string $html): void
    {
        if ('' !== $html) {
            $this->fields[] = new ImportField('explanation', text: $html);
        }
    }

    private function addSubmit(string $label): void
    {
        $this->fields[] = new ImportField('submit', label: $label);
    }

    private function addUnsupportedType(string $type): void
    {
        $this->unsupportedTypes[] = $type;
    }

    /**
     * @param list<Option> $options
     *
     * @return list<Option>
     */
    private function getOptions(array $options): array
    {
        return array_map(static fn (array $option): array => [
            'value' => $option['value'],
            'label' => $option['label'],
        ], $options);
    }

    private function toPlainText(string $html): string
    {
        return $this->htmlDecoder->htmlToPlainText($html, true);
    }
}
