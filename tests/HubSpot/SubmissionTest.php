<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Tests\HubSpot;

use DigitaleDinge\HubSpotBundle\HubSpot\LegalBasis;
use DigitaleDinge\HubSpotBundle\HubSpot\ObjectType;
use DigitaleDinge\HubSpotBundle\HubSpot\Submission;
use PHPUnit\Framework\TestCase;

final class SubmissionTest extends TestCase
{
    public function testSendsFieldsAsProperties(): void
    {
        $submission = new Submission()
            ->addField('email', 'jane@example.com')
            ->addField('name', 'Digitale Dinge', ObjectType::Company)
            ->addField('interests', ['web', 'print'])
        ;

        $this->assertSame(
            [
                'fields' => [
                    [
                        'objectTypeId' => '0-1',
                        'name' => 'email',
                        'value' => 'jane@example.com',
                    ],
                    [
                        'objectTypeId' => '0-2',
                        'name' => 'name',
                        'value' => 'Digitale Dinge',
                    ],
                    [
                        'objectTypeId' => '0-1',
                        'name' => 'interests',
                        'value' => 'web;print',
                    ],
                ],
            ],
            $submission->jsonSerialize(),
        );
    }

    public function testSendsTheContext(): void
    {
        $submission = new Submission('https://example.com/contact', 'abc123');

        $this->assertSame(
            [
                'fields' => [],
                'context' => [
                    'pageUri' => 'https://example.com/contact',
                    'hutk' => 'abc123',
                ],
            ],
            $submission->jsonSerialize(),
        );
    }

    public function testOmitsAMissingTrackingCookie(): void
    {
        $submission = new Submission('https://example.com/contact');

        $this->assertSame(
            [
                'pageUri' => 'https://example.com/contact',
            ],
            $submission->jsonSerialize()['context'],
        );
    }

    public function testSendsSeparateConsentCheckboxes(): void
    {
        $submission = new Submission()
            ->setProcessingConsent(true, 'I agree to the processing of my data.')
            ->addCommunicationConsent(123, true, 'Send me the newsletter.')
            ->addCommunicationConsent(456, false, 'Send me event invitations.')
        ;

        $this->assertSame(
            [
                'consent' => [
                    'consentToProcess' => true,
                    'text' => 'I agree to the processing of my data.',
                    'communications' => [
                        [
                            'value' => true,
                            'subscriptionTypeId' => 123,
                            'text' => 'Send me the newsletter.',
                        ],
                        [
                            'value' => false,
                            'subscriptionTypeId' => 456,
                            'text' => 'Send me event invitations.',
                        ],
                    ],
                ],
            ],
            $submission->jsonSerialize()['legalConsentOptions'],
        );
    }

    public function testSendsImplicitConsentToProcess(): void
    {
        $submission = new Submission()->setProcessingConsent(true, 'By submitting this form you agree to the processing of your data.');

        $this->assertSame(
            [
                'consent' => [
                    'consentToProcess' => true,
                    'text' => 'By submitting this form you agree to the processing of your data.',
                ],
            ],
            $submission->jsonSerialize()['legalConsentOptions'],
        );
    }

    public function testSendsAMissingProcessingConsentAsFalse(): void
    {
        $submission = new Submission()->setProcessingConsent(false, 'I agree to the processing of my data.');

        $this->assertSame(
            [
                'consent' => [
                    'consentToProcess' => false,
                    'text' => 'I agree to the processing of my data.',
                ],
            ],
            $submission->jsonSerialize()['legalConsentOptions'],
        );
    }

    public function testSendsCommunicationConsentWithoutProcessingConsentAsIs(): void
    {
        $submission = new Submission()->addCommunicationConsent(123, true, 'Send me the newsletter.');

        $this->assertSame(
            [
                'consent' => [
                    'communications' => [
                        [
                            'value' => true,
                            'subscriptionTypeId' => 123,
                            'text' => 'Send me the newsletter.',
                        ],
                    ],
                ],
            ],
            $submission->jsonSerialize()['legalConsentOptions'],
        );
    }

    public function testSendsLegitimateInterest(): void
    {
        $submission = new Submission()->setLegitimateInterest(123, LegalBasis::Lead, 'We will contact you about our services.');

        $this->assertSame(
            [
                'legitimateInterest' => [
                    'value' => true,
                    'subscriptionTypeId' => 123,
                    'legalBasis' => 'LEAD',
                    'text' => 'We will contact you about our services.',
                ],
            ],
            $submission->jsonSerialize()['legalConsentOptions'],
        );
    }

    public function testOmitsLegalConsentOptionsWithoutConsent(): void
    {
        $submission = new Submission()->addField('email', 'jane@example.com');

        $this->assertArrayNotHasKey('legalConsentOptions', $submission->jsonSerialize());
    }
}
