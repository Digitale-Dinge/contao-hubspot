<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Tests\HubSpot;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use DigitaleDinge\HubSpotBundle\HubSpot\LegalBasis;
use DigitaleDinge\HubSpotBundle\HubSpot\LegalConsent;
use DigitaleDinge\HubSpotBundle\HubSpot\ObjectType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class TranslatableLabelTest extends TestCase
{
    #[DataProvider('provideCases')]
    public function testTranslatesTheLabelInEveryLanguage(TranslatableLabelInterface $case): void
    {
        $label = $case->label();

        $this->assertSame('hubspot', $label->getDomain());

        foreach (['de', 'en'] as $language) {
            $translations = Yaml::parseFile(__DIR__.'/../../translations/'.$language.'/hubspot.'.$language.'.yaml');
            [$group, $key] = explode('.', $label->getMessage(), 2);

            $this->assertIsArray($translations);
            $this->assertIsArray($translations[$group]);
            $this->assertArrayHasKey($key, $translations[$group], $language);
        }
    }

    public static function provideCases(): iterable
    {
        foreach ([...ObjectType::cases(), ...LegalConsent::cases(), ...LegalBasis::cases()] as $case) {
            yield $case::class.'::'.$case->name => [$case];
        }
    }

    public function testReturnsTheLegalConsentOptionsOfAFieldType(): void
    {
        $this->assertSame([LegalConsent::Processing, LegalConsent::Communication], LegalConsent::forFieldType('checkbox'));
        $this->assertSame([LegalConsent::Processing, LegalConsent::LegitimateInterest], LegalConsent::forFieldType('explanation'));
        $this->assertSame([], LegalConsent::forFieldType('text'));
    }
}
