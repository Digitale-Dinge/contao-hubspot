<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use DigitaleDinge\HubSpotBundle\HubSpot\LegalConsent;

class LegalConsentOptionsListener
{
    /**
     * @return list<string>
     */
    #[AsCallback('tl_form_field', 'fields.dd_hubspot_legal.options')]
    public function __invoke(DataContainer|null $dc = null): array
    {
        $type = $dc?->getCurrentRecord()['type'] ?? '';

        return array_map(
            static fn (LegalConsent $case): string => $case->value,
            LegalConsent::forFieldType(\is_string($type) ? $type : ''),
        );
    }
}
