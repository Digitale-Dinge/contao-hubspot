<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use DigitaleDinge\HubSpotBundle\HubSpot\FormListLoader;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @experimental
 */
class HubSpotFormIdListener
{
    public function __construct(
        private readonly FormListLoader $formListLoader,
        private readonly RequestStack $requestStack,
    ) {
    }

    #[AsCallback('tl_form', 'config.onload')]
    public function __invoke(DataContainer|null $dc = null): void
    {
        if ('edit' !== $this->requestStack->getCurrentRequest()?->query->get('act')) {
            return;
        }

        $record = $dc?->getCurrentRecord() ?? [];

        if (empty($record['dd_hubspot'])) {
            return;
        }

        $forms = $this->formListLoader->load();

        if ([] === $forms) {
            return;
        }

        $current = $record['dd_hubspot_form_id'] ?? '';

        if (\is_string($current) && '' !== $current && !isset($forms[$current])) {
            $forms[$current] = $current;
        }

        $GLOBALS['TL_DCA']['tl_form']['fields']['dd_hubspot_form_id']['inputType'] = 'select';
        $GLOBALS['TL_DCA']['tl_form']['fields']['dd_hubspot_form_id']['options'] = $forms;
        $GLOBALS['TL_DCA']['tl_form']['fields']['dd_hubspot_form_id']['eval']['includeBlankOption'] = true;
        $GLOBALS['TL_DCA']['tl_form']['fields']['dd_hubspot_form_id']['eval']['chosen'] = true;
    }
}
