<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Tests\Import;

use DigitaleDinge\HubSpotBundle\HubSpot\LegalConsent;
use DigitaleDinge\HubSpotBundle\HubSpot\ObjectType;
use DigitaleDinge\HubSpotBundle\Import\FormFieldImporter;
use DigitaleDinge\HubSpotBundle\Import\ImportField;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

final class FormFieldImporterTest extends TestCase
{
    public function testAppendsTheFieldsToTheForm(): void
    {
        $rows = [];

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('fetchOne')
            ->with('SELECT MAX(sorting) FROM tl_form_field WHERE pid = ?', [5])
            ->willReturn(256)
        ;

        $connection
            ->expects($this->exactly(2))
            ->method('insert')
            ->willReturnCallback(
                static function (string $table, array $row) use (&$rows): int {
                    $rows[] = $row;

                    return 1;
                },
            )
        ;

        new FormFieldImporter($connection)->import(5, [
            new ImportField('select', name: 'salutation', label: 'Salutation & title', mandatory: true, options: [[
                'value' => 'Mr',
                'label' => 'Mr',
            ]], objectType: ObjectType::Contact),
            new ImportField('checkbox', name: 'subscription_type_123', options: [[
                'value' => 'true',
                'label' => 'Newsletter',
            ]], legalConsent: LegalConsent::Communication, subscriptionTypeId: 123),
        ]);

        $this->assertSame(
            ['select', 'salutation', 'Salutation &amp; title', 1, 'a:1:{i:0;a:2:{s:5:"value";s:2:"Mr";s:5:"label";s:2:"Mr";}}', 1, '0-1', '', 5, 384],
            [$rows[0]['type'], $rows[0]['name'], $rows[0]['label'], $rows[0]['mandatory'], $rows[0]['options'], $rows[0]['dd_hubspot'], $rows[0]['dd_hubspot_object_type'], $rows[0]['dd_hubspot_legal'], $rows[0]['pid'], $rows[0]['sorting']],
        );

        $this->assertSame(
            ['checkbox', 1, 'communication', 123, 512],
            [$rows[1]['type'], $rows[1]['dd_hubspot'], $rows[1]['dd_hubspot_legal'], $rows[1]['dd_hubspot_subscription_type_id'], $rows[1]['sorting']],
        );
    }
}
