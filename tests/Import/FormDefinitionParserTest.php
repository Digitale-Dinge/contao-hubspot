<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Tests\Import;

use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\CoreBundle\String\HtmlDecoder;
use DigitaleDinge\HubSpotBundle\HubSpot\LegalBasis;
use DigitaleDinge\HubSpotBundle\HubSpot\LegalConsent;
use DigitaleDinge\HubSpotBundle\HubSpot\ObjectType;
use DigitaleDinge\HubSpotBundle\Import\FormDefinitionParser;
use DigitaleDinge\HubSpotBundle\Import\ImportField;
use PHPUnit\Framework\TestCase;

final class FormDefinitionParserTest extends TestCase
{
    public function testParsesTheFieldsOfARenderDefinition(): void
    {
        $definition = $this->createParser()->parseRenderDefinition([
            'form' => [
                'modules' => [
                    [
                        'type' => 'step',
                        'modules' => [
                            [
                                'type' => 'row',
                                'modules' => [[
                                    'type' => 'heading',
                                    'text' => 'Contact us',
                                    'level' => 1,
                                ]],
                            ],
                            [
                                'type' => 'row',
                                'modules' => [[
                                    'type' => 'richText',
                                    'html' => '<p>Please fill out the form.</p>',
                                ]],
                            ],
                            [
                                'type' => 'row',
                                'modules' => [[
                                    'type' => 'email',
                                    'label' => 'Email',
                                    'required' => true,
                                    'propertyReference' => '0-1/email',
                                ]],
                            ],
                            [
                                'type' => 'row',
                                'modules' => [[
                                    'type' => 'radioSelect',
                                    'label' => 'Type',
                                    'required' => false,
                                    'propertyReference' => '0-2/type',
                                    'options' => [[
                                        'label' => 'Partner',
                                        'value' => 'PARTNER',
                                        'selected' => false,
                                    ]],
                                ]],
                            ],
                            [
                                'type' => 'row',
                                'modules' => [[
                                    'type' => 'hidden',
                                    'propertyReference' => '0-1/lead_source',
                                    'values' => ['Website'],
                                ]],
                            ],
                            [
                                'type' => 'row',
                                'modules' => [[
                                    'type' => 'hidden',
                                    'propertyReference' => '0-1/utm_source',
                                ]],
                            ],
                            [
                                'type' => 'row',
                                'modules' => [[
                                    'type' => 'fileUpload',
                                    'label' => 'CV',
                                    'required' => false,
                                    'propertyReference' => '0-1/cv',
                                ]],
                            ],
                            [
                                'type' => 'navigationRow',
                                'modules' => [[
                                    'type' => 'submitButton',
                                    'text' => 'Send',
                                ]],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals(
            [
                new ImportField('explanation', text: '<h1>Contact us</h1>'),
                new ImportField('explanation', text: '<p>Please fill out the form.</p>'),
                new ImportField('text', name: 'email', label: 'Email', mandatory: true, rgxp: 'email', objectType: ObjectType::Contact),
                new ImportField('radio', name: 'type', label: 'Type', options: [[
                    'value' => 'PARTNER',
                    'label' => 'Partner',
                ]], objectType: ObjectType::Company),
                new ImportField('hidden', name: 'lead_source', value: 'Website', objectType: ObjectType::Contact),
                new ImportField('hidden', name: 'utm_source', objectType: ObjectType::Contact),
                new ImportField('submit', label: 'Send'),
            ],
            $definition->fields,
        );
        $this->assertSame(['fileUpload'], $definition->unsupportedTypes);
    }

    public function testParsesConsentCheckboxes(): void
    {
        $definition = $this->createParser()->parseRenderDefinition([
            'form' => [
                'modules' => [[
                    'type' => 'dataPrivacy',
                    'isLegitimateInterest' => false,
                    'processingConsentType' => 'REQUIRED_CHECKBOX',
                    'communicationConsentText' => 'By checking the boxes below, you agree to receive communications.',
                    'communicationConsentCheckboxes' => [
                        [
                            'communicationTypeId' => 123,
                            'label' => 'Send me the newsletter.',
                            'required' => true,
                        ],
                        [
                            'communicationTypeId' => 456,
                            'label' => '<p>Send me event invitations.</p>',
                            'required' => false,
                        ],
                    ],
                    'processingConsentText' => 'To process your request, we need your permission.',
                    'processingConsentCheckboxLabel' => 'I agree to the processing of my personal data.',
                    'privacyPolicyText' => 'We care about your privacy.',
                ]],
            ],
        ]);

        $this->assertEquals(
            [
                new ImportField('explanation', text: '<p>By checking the boxes below, you agree to receive communications.</p>'),
                new ImportField(
                    'checkbox',
                    name: 'subscription_type_123',
                    mandatory: true,
                    options: [[
                        'value' => 'true',
                        'label' => 'Send me the newsletter.',
                    ]],
                    legalConsent: LegalConsent::Communication,
                    subscriptionTypeId: 123,
                ),
                new ImportField(
                    'checkbox',
                    name: 'subscription_type_456',
                    options: [[
                        'value' => 'true',
                        'label' => 'Send me event invitations.',
                    ]],
                    legalConsent: LegalConsent::Communication,
                    subscriptionTypeId: 456,
                ),
                new ImportField('explanation', text: '<p>To process your request, we need your permission.</p>'),
                new ImportField(
                    'checkbox',
                    name: 'processing_consent',
                    mandatory: true,
                    options: [[
                        'value' => 'true',
                        'label' => 'I agree to the processing of my personal data.',
                    ]],
                    legalConsent: LegalConsent::Processing,
                ),
                new ImportField('explanation', text: '<p>We care about your privacy.</p>'),
            ],
            $definition->fields,
        );
    }

    public function testParsesImplicitConsentToProcess(): void
    {
        $definition = $this->createParser()->parseRenderDefinition([
            'form' => [
                'modules' => [[
                    'type' => 'dataPrivacy',
                    'processingConsentType' => 'IMPLICIT',
                    'processingConsentText' => 'By clicking submit, you consent to the processing of your data.',
                ]],
            ],
        ]);

        $this->assertEquals(
            [new ImportField('explanation', text: '<p>By clicking submit, you consent to the processing of your data.</p>', legalConsent: LegalConsent::Processing)],
            $definition->fields,
        );
    }

    public function testParsesLegitimateInterest(): void
    {
        $definition = $this->createParser()->parseRenderDefinition([
            'form' => [
                'modules' => [[
                    'type' => 'dataPrivacy',
                    'isLegitimateInterest' => true,
                    'legitimateInterestLegalBasis' => 'LEGITIMATE_INTEREST_CLIENT',
                    'legitimateInterestSubscriptionTypes' => [789, 321],
                    'processingConsentType' => 'IMPLICIT',
                    'privacyPolicyText' => 'We will contact you about our products & services.',
                ]],
            ],
        ]);

        $this->assertEquals(
            [new ImportField(
                'explanation',
                text: '<p>We will contact you about our products &amp; services.</p>',
                legalConsent: LegalConsent::LegitimateInterest,
                subscriptionTypeId: 789,
                legalBasis: LegalBasis::Customer,
            )],
            $definition->fields,
        );
    }

    public function testParsesAnEmbedDefinition(): void
    {
        $definition = $this->createParser()->parseEmbedDefinition([
            'form' => [
                'submitText' => 'Submit',
                'formFieldGroups' => [
                    [
                        'fields' => [[
                            'name' => 'lastname',
                            'label' => 'Last name',
                            'fieldType' => 'text',
                            'required' => true,
                            'hidden' => false,
                            'objectTypeId' => '0-1',
                            'defaultValue' => '',
                            'options' => [],
                        ]],
                    ],
                    [
                        'fields' => [[
                            'name' => 'utm_source',
                            'label' => '',
                            'fieldType' => 'text',
                            'required' => false,
                            'hidden' => true,
                            'objectTypeId' => '0-1',
                            'defaultValue' => 'website',
                            'options' => [],
                        ]],
                    ],
                    [
                        'fields' => [],
                        'richText' => [
                            'content' => '<p>Thank you!</p>',
                        ],
                    ],
                    [
                        'fields' => [[
                            'name' => 'cv',
                            'label' => 'CV',
                            'fieldType' => 'file',
                            'required' => false,
                            'hidden' => false,
                            'objectTypeId' => '0-1',
                            'defaultValue' => '',
                            'options' => [],
                        ]],
                    ],
                ],
                'metaData' => [
                    [
                        'name' => 'lang',
                        'value' => 'en',
                    ],
                    [
                        'name' => 'legalConsentOptions',
                        'value' => json_encode([
                            'processingConsentType' => 'IMPLICIT',
                            'processingConsentText' => '<p>By clicking submit, you consent.</p>',
                        ]),
                    ],
                ],
            ],
        ]);

        $this->assertEquals(
            [
                new ImportField('text', name: 'lastname', label: 'Last name', mandatory: true, objectType: ObjectType::Contact),
                new ImportField('hidden', name: 'utm_source', value: 'website', objectType: ObjectType::Contact),
                new ImportField('explanation', text: '<p>Thank you!</p>'),
                new ImportField('explanation', text: '<p>By clicking submit, you consent.</p>', legalConsent: LegalConsent::Processing),
                new ImportField('submit', label: 'Submit'),
            ],
            $definition->fields,
        );
        $this->assertSame(['file'], $definition->unsupportedTypes);
    }

    public function testParsesAllPropertyTypesOfARenderDefinition(): void
    {
        $option = [
            'label' => 'Web',
            'value' => 'web',
        ];

        $definition = $this->createParser()->parseRenderDefinition([
            'form' => [
                'modules' => [
                    [
                        'type' => 'phone',
                        'label' => 'Phone',
                        'required' => false,
                        'propertyReference' => '0-1/phone',
                    ],
                    [
                        'type' => 'number',
                        'label' => 'Employees',
                        'required' => false,
                        'propertyReference' => '0-2/numberofemployees',
                    ],
                    [
                        'type' => 'multiLineText',
                        'label' => 'Message',
                        'required' => false,
                        'propertyReference' => '0-1/message',
                    ],
                    [
                        'type' => 'dropdownSelect',
                        'label' => 'Country',
                        'required' => false,
                        'propertyReference' => '0-1/country',
                        'options' => [$option],
                    ],
                    [
                        'type' => 'multipleCheckbox',
                        'label' => 'Interests',
                        'required' => false,
                        'propertyReference' => '0-1/interests',
                        'options' => [$option],
                    ],
                    [
                        'type' => 'singleCheckbox',
                        'label' => 'Existing customer',
                        'required' => false,
                        'propertyReference' => '0-1/customer',
                    ],
                ],
            ],
        ]);

        $this->assertEquals(
            [
                new ImportField('text', name: 'phone', label: 'Phone', objectType: ObjectType::Contact),
                new ImportField('text', name: 'numberofemployees', label: 'Employees', rgxp: 'digit', objectType: ObjectType::Company),
                new ImportField('textarea', name: 'message', label: 'Message', objectType: ObjectType::Contact),
                new ImportField('select', name: 'country', label: 'Country', options: [[
                    'value' => 'web',
                    'label' => 'Web',
                ]], objectType: ObjectType::Contact),
                new ImportField('checkbox', name: 'interests', label: 'Interests', options: [[
                    'value' => 'web',
                    'label' => 'Web',
                ]], objectType: ObjectType::Contact),
                new ImportField('checkbox', name: 'customer', label: 'Existing customer', options: [[
                    'value' => 'true',
                    'label' => 'Existing customer',
                ]], objectType: ObjectType::Contact),
            ],
            $definition->fields,
        );
    }

    public function testParsesAllFieldTypesOfAnEmbedDefinition(): void
    {
        $field = [
            'required' => false,
            'hidden' => false,
            'objectTypeId' => '0-1',
            'defaultValue' => '',
            'options' => [],
        ];

        $option = [
            'label' => 'Web',
            'value' => 'web',
        ];

        $definition = $this->createParser()->parseEmbedDefinition([
            'form' => [
                'submitText' => 'Submit',
                'metaData' => [],
                'formFieldGroups' => [[
                    'fields' => [
                        [
                            ...$field,
                            'name' => 'email',
                            'label' => 'Email',
                            'fieldType' => 'text',
                        ],
                        [
                            ...$field,
                            'name' => 'phone',
                            'label' => 'Phone',
                            'fieldType' => 'phonenumber',
                        ],
                        [
                            ...$field,
                            'name' => 'employees',
                            'label' => 'Employees',
                            'fieldType' => 'number',
                        ],
                        [
                            ...$field,
                            'name' => 'message',
                            'label' => 'Message',
                            'fieldType' => 'textarea',
                        ],
                        [
                            ...$field,
                            'name' => 'country',
                            'label' => 'Country',
                            'fieldType' => 'select',
                            'options' => [$option],
                        ],
                        [
                            ...$field,
                            'name' => 'source',
                            'label' => 'Source',
                            'fieldType' => 'radio',
                            'options' => [$option],
                        ],
                        [
                            ...$field,
                            'name' => 'interests',
                            'label' => 'Interests',
                            'fieldType' => 'checkbox',
                            'options' => [$option],
                        ],
                        [
                            ...$field,
                            'name' => 'customer',
                            'label' => 'Existing customer',
                            'fieldType' => 'booleancheckbox',
                        ],
                    ],
                ]],
            ],
        ]);

        $this->assertEquals(
            [
                new ImportField('text', name: 'email', label: 'Email', rgxp: 'email', objectType: ObjectType::Contact),
                new ImportField('text', name: 'phone', label: 'Phone', objectType: ObjectType::Contact),
                new ImportField('text', name: 'employees', label: 'Employees', rgxp: 'digit', objectType: ObjectType::Contact),
                new ImportField('textarea', name: 'message', label: 'Message', objectType: ObjectType::Contact),
                new ImportField('select', name: 'country', label: 'Country', options: [[
                    'value' => 'web',
                    'label' => 'Web',
                ]], objectType: ObjectType::Contact),
                new ImportField('radio', name: 'source', label: 'Source', options: [[
                    'value' => 'web',
                    'label' => 'Web',
                ]], objectType: ObjectType::Contact),
                new ImportField('checkbox', name: 'interests', label: 'Interests', options: [[
                    'value' => 'web',
                    'label' => 'Web',
                ]], objectType: ObjectType::Contact),
                new ImportField('checkbox', name: 'customer', label: 'Existing customer', options: [[
                    'value' => 'true',
                    'label' => 'Existing customer',
                ]], objectType: ObjectType::Contact),
                new ImportField('submit', label: 'Submit'),
            ],
            $definition->fields,
        );
    }

    public function testSkipsPropertiesOfUnsupportedObjectTypes(): void
    {
        $definition = $this->createParser()->parseRenderDefinition([
            'form' => [
                'modules' => [
                    [
                        'type' => 'text',
                        'propertyReference' => '0-3/dealname',
                        'label' => 'Deal',
                        'required' => false,
                    ],
                    [
                        'type' => 'text',
                        'label' => 'Unknown',
                        'required' => false,
                    ],
                ],
            ],
        ]);

        $this->assertSame([], $definition->fields);
        $this->assertSame(['0-3/dealname', 'text'], $definition->unsupportedTypes);
    }

    public function testSkipsEmbedFieldsOfUnsupportedObjectTypes(): void
    {
        $definition = $this->createParser()->parseEmbedDefinition([
            'form' => [
                'submitText' => 'Submit',
                'formFieldGroups' => [[
                    'fields' => [[
                        'name' => 'dealname',
                        'label' => 'Deal',
                        'fieldType' => 'text',
                        'required' => false,
                        'hidden' => false,
                        'objectTypeId' => '0-3',
                        'defaultValue' => '',
                        'options' => [],
                    ]],
                ]],
                'metaData' => [],
            ],
        ]);

        $this->assertEquals([new ImportField('submit', label: 'Submit')], $definition->fields);
        $this->assertSame(['0-3/dealname'], $definition->unsupportedTypes);
    }

    private function createParser(): FormDefinitionParser
    {
        return new FormDefinitionParser(new HtmlDecoder($this->createStub(InsertTagParser::class)));
    }
}
