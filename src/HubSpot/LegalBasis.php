<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\HubSpot;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

enum LegalBasis: string implements TranslatableLabelInterface
{
    case Lead = 'LEAD';
    case Customer = 'CUSTOMER';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage('legal_basis.'.$this->name, [], 'hubspot');
    }
}
