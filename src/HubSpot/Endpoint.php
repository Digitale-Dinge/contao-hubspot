<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\HubSpot;

enum Endpoint: string
{
    case Submit = 'https://api.hsforms.com/submissions/v3/integration/submit/{portalId}/{formId}';

    /**
     * @experimental
     */
    case Form = 'https://api.hubapi.com/marketing/v3/forms/{formId}';

    /**
     * @experimental
     */
    case Forms = 'https://api.hubapi.com/marketing/v3/forms/';

    /**
     * @internal
     *
     * @experimental
     */
    case RenderDefinition = 'https://forms.hsforms.com/embed/v4/render-definition/{portalId}/{formId}';

    /**
     * @internal
     *
     * @experimental
     */
    case EmbedDefinition = 'https://forms.hsforms.com/embed/v3/form/{portalId}/{formId}/json';

    public function getUrl(string $portalId, string $formId): string
    {
        return strtr($this->value, [
            '{portalId}' => $portalId,
            '{formId}' => $formId,
        ]);
    }
}
