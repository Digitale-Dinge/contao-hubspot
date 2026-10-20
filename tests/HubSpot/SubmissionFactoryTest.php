<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Tests\HubSpot;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\CoreBundle\String\HtmlDecoder;
use Contao\FormFieldModel;
use DigitaleDinge\HubSpotBundle\HubSpot\SubmissionFactory;
use DigitaleDinge\HubSpotBundle\Tests\Fixtures\FormFieldModelAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class SubmissionFactoryTest extends TestCase
{
    public function testSendsSubmittedFieldsAsProperties(): void
    {
        $payload = $this->createPayload(
            [
                $this->createField([
                    'name' => 'email',
                ]),
                $this->createField([
                    'name' => 'company',
                    'dd_hubspot_object_type' => '0-2',
                ]),
                $this->createField([
                    'name' => 'interests',
                    'type' => 'checkbox',
                ]),
                $this->createField([
                    'name' => 'not_submitted',
                ]),
            ],
            [
                'email' => 'jane@example.com',
                'company' => 'Digitale Dinge',
                'interests' => ['web', 'print'],
            ],
        );

        $this->assertSame(
            [
                [
                    'objectTypeId' => '0-1',
                    'name' => 'email',
                    'value' => 'jane@example.com',
                ],
                [
                    'objectTypeId' => '0-2',
                    'name' => 'company',
                    'value' => 'Digitale Dinge',
                ],
                [
                    'objectTypeId' => '0-1',
                    'name' => 'interests',
                    'value' => 'web;print',
                ],
            ],
            $payload['fields'],
        );
    }

    public function testOnlySendsFieldsMarkedForHubSpot(): void
    {
        $payload = $this->createPayload(
            [
                $this->createField([
                    'name' => 'email',
                ]),
                $this->createField([
                    'name' => 'utm_source',
                    'type' => 'hidden',
                    'dd_hubspot' => '',
                ]),
            ],
            [
                'email' => 'jane@example.com',
                'utm_source' => 'newsletter',
            ],
        );

        $this->assertSame(
            [[
                'objectTypeId' => '0-1',
                'name' => 'email',
                'value' => 'jane@example.com',
            ]],
            $payload['fields'],
        );
    }

    public function testSkipsEmptyValues(): void
    {
        $payload = $this->createPayload(
            [
                $this->createField([
                    'name' => 'email',
                ]),
                $this->createField([
                    'name' => 'lead_source',
                    'type' => 'hidden',
                ]),
                $this->createField([
                    'name' => 'interests',
                    'type' => 'checkbox',
                ]),
            ],
            [
                'email' => 'jane@example.com',
                'lead_source' => '',
                'interests' => [],
            ],
        );

        $this->assertSame(
            [[
                'objectTypeId' => '0-1',
                'name' => 'email',
                'value' => 'jane@example.com',
            ]],
            $payload['fields'],
        );
    }

    public function testDoesNotSendConsentFieldsNotMarkedForHubSpot(): void
    {
        $payload = $this->createPayload(
            [
                $this->createField([
                    'name' => 'privacy',
                    'type' => 'checkbox',
                    'dd_hubspot' => '',
                    'dd_hubspot_legal' => 'processing',
                ]),
            ],
            [
                'privacy' => '1',
            ],
        );

        $this->assertArrayNotHasKey('legalConsentOptions', $payload);
    }

    public function testSendsTheOptionLabelOfACheckedProcessingCheckbox(): void
    {
        $payload = $this->createPayload(
            [
                $this->createField([
                    'name' => 'privacy',
                    'type' => 'checkbox',
                    'dd_hubspot_legal' => 'processing',
                    'label' => 'Privacy',
                    'options' => serialize([[
                        'value' => '1',
                        'label' => 'I agree to the <a href="/privacy">privacy policy</a>.',
                    ]]),
                ]),
            ],
            [
                'privacy' => '1',
            ],
        );

        $this->assertSame(
            [
                'consent' => [
                    'consentToProcess' => true,
                    'text' => 'I agree to the privacy policy.',
                ],
            ],
            $payload['legalConsentOptions'],
        );
        $this->assertSame([], $payload['fields']);
    }

    public function testFallsBackToTheFieldLabelWithoutOptionLabels(): void
    {
        $payload = $this->createPayload(
            [
                $this->createField([
                    'name' => 'privacy',
                    'type' => 'checkbox',
                    'dd_hubspot_legal' => 'processing',
                    'label' => 'I agree to the privacy policy.',
                ]),
            ],
            [
                'privacy' => '1',
            ],
        );

        $this->assertSame(
            [
                'consent' => [
                    'consentToProcess' => true,
                    'text' => 'I agree to the privacy policy.',
                ],
            ],
            $payload['legalConsentOptions'],
        );
    }

    public function testSendsAnUncheckedCommunicationCheckboxAsFalse(): void
    {
        $payload = $this->createPayload(
            [
                $this->createField([
                    'name' => 'newsletter',
                    'type' => 'checkbox',
                    'dd_hubspot_legal' => 'communication',
                    'dd_hubspot_subscription_type_id' => '123',
                    'label' => 'Send me the newsletter.',
                ]),
            ],
            [
                'newsletter' => '',
            ],
        );

        $this->assertSame(
            [
                'consent' => [
                    'communications' => [[
                        'value' => false,
                        'subscriptionTypeId' => 123,
                        'text' => 'Send me the newsletter.',
                    ]],
                ],
            ],
            $payload['legalConsentOptions'],
        );
    }

    public function testAnExplanationGivesImplicitConsentToProcess(): void
    {
        $payload = $this->createPayload(
            [
                $this->createField([
                    'type' => 'explanation',
                    'dd_hubspot_legal' => 'processing',
                    'text' => '<p>By submitting this form you agree to the processing of your data.</p>',
                ]),
            ],
            [],
        );

        $this->assertSame(
            [
                'consent' => [
                    'consentToProcess' => true,
                    'text' => 'By submitting this form you agree to the processing of your data.',
                ],
            ],
            $payload['legalConsentOptions'],
        );
    }

    public function testAnExplanationSendsLegitimateInterest(): void
    {
        $payload = $this->createPayload(
            [
                $this->createField([
                    'type' => 'explanation',
                    'dd_hubspot_legal' => 'legitimate_interest',
                    'dd_hubspot_legal_basis' => 'CUSTOMER',
                    'dd_hubspot_subscription_type_id' => '456',
                    'text' => '<p>We will contact you about our services.</p>',
                ]),
            ],
            [],
        );

        $this->assertSame(
            [
                'legitimateInterest' => [
                    'value' => true,
                    'subscriptionTypeId' => 456,
                    'legalBasis' => 'CUSTOMER',
                    'text' => 'We will contact you about our services.',
                ],
            ],
            $payload['legalConsentOptions'],
        );
    }

    public function testSendsThePageAndTheTrackingCookie(): void
    {
        $request = Request::create('https://example.com/contact?foo=bar', cookies: [
            'hubspotutk' => 'abc123',
        ]);

        $payload = $this->createFactory([])->create(1, [], $request)->jsonSerialize();

        $this->assertSame(
            [
                'pageUri' => 'https://example.com/contact',
                'hutk' => 'abc123',
            ],
            $payload['context'],
        );
    }

    /**
     * @param list<FormFieldModel> $fields
     * @param array<string, mixed> $submittedData
     *
     * @return array<string, mixed>
     */
    private function createPayload(array $fields, array $submittedData): array
    {
        return $this->createFactory($fields)->create(1, $submittedData, Request::create('https://example.com/contact'))->jsonSerialize();
    }

    /**
     * @param list<FormFieldModel> $fields
     */
    private function createFactory(array $fields): SubmissionFactory
    {
        $adapter = $this->createMock(FormFieldModelAdapter::class);
        $adapter
            ->expects($this->once())
            ->method('findPublishedByPid')
            ->with(1)
            ->willReturn($fields)
        ;

        $framework = $this->createStub(ContaoFramework::class);
        $framework
            ->method('getAdapter')
            ->willReturn($adapter)
        ;

        $insertTagParser = $this->createStub(InsertTagParser::class);
        $insertTagParser
            ->method('replaceInline')
            ->willReturnArgument(0)
        ;

        return new SubmissionFactory($framework, new HtmlDecoder($insertTagParser));
    }

    /**
     * @param array<string, string> $properties
     */
    private function createField(array $properties): FormFieldModel
    {
        $properties = [
            'type' => 'text',
            'name' => '',
            'label' => '',
            'text' => '',
            'options' => null,
            'dd_hubspot' => '1',
            'dd_hubspot_object_type' => '0-1',
            'dd_hubspot_legal' => '',
            'dd_hubspot_legal_basis' => 'LEAD',
            'dd_hubspot_subscription_type_id' => '0',
            ...$properties,
        ];

        $field = $this->createStub(FormFieldModel::class);
        $field
            ->method('__get')
            ->willReturnCallback(static fn (string $key): mixed => $properties[$key])
        ;

        return $field;
    }
}
