<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\HubSpot;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\String\HtmlDecoder;
use Contao\FormFieldModel;
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\Request;

readonly class SubmissionFactory
{
    public function __construct(
        private ContaoFramework $framework,
        private HtmlDecoder $htmlDecoder,
    ) {
    }

    /**
     * @param array<string, mixed> $submittedData
     */
    public function create(int $formId, array $submittedData, Request $request): Submission
    {
        $submission = new Submission(
            $request->getSchemeAndHttpHost().$request->getPathInfo(),
            $request->cookies->getString('hubspotutk'),
        );

        $fields = $this->framework->getAdapter(FormFieldModel::class)->findPublishedByPid($formId) ?? [];

        foreach ($fields as $field) {
            $this->addField($submission, $field, $submittedData);
        }

        return $submission;
    }

    /**
     * @param array<string, mixed> $submittedData
     */
    private function addField(Submission $submission, FormFieldModel $field, array $submittedData): void
    {
        if (!$field->dd_hubspot) {
            return;
        }

        match (LegalConsent::tryFrom($this->toString($field->dd_hubspot_legal))) {
            LegalConsent::Processing => $submission->setProcessingConsent(
                $this->isConsentGiven($field, $submittedData),
                $this->getConsentText($field),
            ),
            LegalConsent::Communication => $submission->addCommunicationConsent(
                $this->toInt($field->dd_hubspot_subscription_type_id),
                $this->isConsentGiven($field, $submittedData),
                $this->getConsentText($field),
            ),
            LegalConsent::LegitimateInterest => $submission->setLegitimateInterest(
                $this->toInt($field->dd_hubspot_subscription_type_id),
                LegalBasis::from($this->toString($field->dd_hubspot_legal_basis)),
                $this->getConsentText($field),
            ),
            null => $this->addProperty($submission, $field, $submittedData),
        };
    }

    /**
     * @param array<string, mixed> $submittedData
     */
    private function addProperty(Submission $submission, FormFieldModel $field, array $submittedData): void
    {
        $value = $this->getSubmittedValue($submittedData[$field->name] ?? '');

        // HubSpot keeps the existing property value for empty values so they are skipped instead of sent
        if ('' === $value || [] === $value) {
            return;
        }

        $submission->addField($field->name, $value, ObjectType::from($this->toString($field->dd_hubspot_object_type)));
    }

    /**
     * @return string|list<string>
     */
    private function getSubmittedValue(mixed $value): array|string
    {
        if (\is_array($value)) {
            return array_values(array_filter($value, \is_string(...)));
        }

        return $this->toString($value);
    }

    /**
     * @param array<string, mixed> $submittedData
     */
    private function isConsentGiven(FormFieldModel $field, array $submittedData): bool
    {
        if ('explanation' === $field->type) {
            return true;
        }

        return !empty($submittedData[$field->name]);
    }

    private function getConsentText(FormFieldModel $field): string
    {
        if ('explanation' === $field->type) {
            return $this->htmlDecoder->htmlToPlainText((string) $field->text);
        }

        $optionLabels = array_filter(array_column(StringUtil::deserialize($field->options, true), 'label'), \is_string(...));

        return $this->htmlDecoder->inputEncodedToPlainText(implode(' ', $optionLabels) ?: (string) $field->label);
    }

    private function toString(mixed $value): string
    {
        return \is_scalar($value) ? (string) $value : '';
    }

    private function toInt(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }
}
