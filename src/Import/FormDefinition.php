<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Import;

readonly class FormDefinition
{
    /**
     * @param list<ImportField> $fields
     * @param list<string>      $unsupportedTypes
     */
    public function __construct(
        public array $fields,
        public array $unsupportedTypes = [],
        public bool $captcha = false,
    ) {
    }
}
