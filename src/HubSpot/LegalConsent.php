<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\HubSpot;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

enum LegalConsent: string implements TranslatableLabelInterface
{
    case Processing = 'processing';
    case Communication = 'communication';
    case LegitimateInterest = 'legitimate_interest';

    /**
     * @return list<self>
     */
    public static function forFieldType(string $type): array
    {
        return match ($type) {
            'checkbox' => [self::Processing, self::Communication],
            'explanation' => [self::Processing, self::LegitimateInterest],
            default => [],
        };
    }

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage('legal_consent.'.$this->name, [], 'hubspot');
    }
}
