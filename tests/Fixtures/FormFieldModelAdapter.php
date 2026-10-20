<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Tests\Fixtures;

use Contao\CoreBundle\Framework\Adapter;
use Contao\FormFieldModel;

/**
 * @extends Adapter<object>
 */
class FormFieldModelAdapter extends Adapter
{
    /**
     * @return list<FormFieldModel>|null
     */
    public function findPublishedByPid(mixed ...$arguments): array|null
    {
        return null;
    }
}
