<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\HubSpot;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

enum ObjectType: string implements TranslatableLabelInterface
{
    case Contact = '0-1';
    case Company = '0-2';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage('object_type.'.$this->name, [], 'hubspot');
    }
}
