<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;

class HubSpotSubpaletteListener
{
    #[AsCallback('tl_form_field', 'config.onload')]
    public function __invoke(DataContainer|null $dc = null): void
    {
        $type = $dc?->getCurrentRecord()['type'] ?? '';

        $GLOBALS['TL_DCA']['tl_form_field']['subpalettes']['dd_hubspot'] = match ($type) {
            'checkbox' => 'dd_hubspot_object_type,dd_hubspot_legal',
            'explanation' => 'dd_hubspot_legal',
            default => 'dd_hubspot_object_type',
        };

        $GLOBALS['TL_DCA']['tl_form_field']['fields']['dd_hubspot_legal']['eval']['mandatory'] = 'explanation' === $type;
    }
}
