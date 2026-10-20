<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Tests\EventListener\DataContainer;

use Contao\DataContainer;
use DigitaleDinge\HubSpotBundle\EventListener\DataContainer\LegalConsentOptionsListener;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LegalConsentOptionsListenerTest extends TestCase
{
    #[DataProvider('provideFieldTypes')]
    public function testReturnsTheLegalConsentOptionsOfTheFieldType(string $type, array $options): void
    {
        $dataContainer = $this->createStub(DataContainer::class);
        $dataContainer
            ->method('getCurrentRecord')
            ->willReturn([
                'type' => $type,
            ])
        ;

        $this->assertSame($options, new LegalConsentOptionsListener()($dataContainer));
    }

    public static function provideFieldTypes(): iterable
    {
        yield 'checkbox' => ['checkbox', ['processing', 'communication']];
        yield 'explanation' => ['explanation', ['processing', 'legitimate_interest']];
        yield 'text' => ['text', []];
    }

    public function testReturnsNoOptionsWithoutARecord(): void
    {
        $this->assertSame([], new LegalConsentOptionsListener()());
    }
}
