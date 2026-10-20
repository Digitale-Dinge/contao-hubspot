<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Tests\EventListener\DataContainer;

use Contao\DataContainer;
use DigitaleDinge\HubSpotBundle\EventListener\DataContainer\HubSpotSubpaletteListener;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HubSpotSubpaletteListenerTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['TL_DCA']);

        parent::tearDown();
    }

    #[DataProvider('provideFieldTypes')]
    public function testShowsTheSettingsOfTheFieldType(string $type, string $subpalette, bool $legalConsentMandatory): void
    {
        $dataContainer = $this->createStub(DataContainer::class);
        $dataContainer
            ->method('getCurrentRecord')
            ->willReturn([
                'type' => $type,
            ])
        ;

        new HubSpotSubpaletteListener()($dataContainer);

        $this->assertSame($subpalette, $GLOBALS['TL_DCA']['tl_form_field']['subpalettes']['dd_hubspot']);
        $this->assertSame($legalConsentMandatory, $GLOBALS['TL_DCA']['tl_form_field']['fields']['dd_hubspot_legal']['eval']['mandatory']);
    }

    public static function provideFieldTypes(): iterable
    {
        yield 'checkbox' => ['checkbox', 'dd_hubspot_object_type,dd_hubspot_legal', false];
        yield 'explanation' => ['explanation', 'dd_hubspot_legal', true];
        yield 'text' => ['text', 'dd_hubspot_object_type', false];
    }

    public function testShowsThePropertySettingsWithoutARecord(): void
    {
        new HubSpotSubpaletteListener()();

        $this->assertSame('dd_hubspot_object_type', $GLOBALS['TL_DCA']['tl_form_field']['subpalettes']['dd_hubspot']);
    }
}
