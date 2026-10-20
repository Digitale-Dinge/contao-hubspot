<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Import;

use Doctrine\DBAL\Connection;

readonly class FormFieldImporter
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @param list<ImportField> $fields
     */
    public function import(int $formId, array $fields): void
    {
        $maxSorting = $this->connection->fetchOne('SELECT MAX(sorting) FROM tl_form_field WHERE pid = ?', [$formId]);
        $sorting = is_numeric($maxSorting) ? (int) $maxSorting : 0;

        foreach ($fields as $field) {
            $sorting += 128;

            $this->connection->insert('tl_form_field', [
                ...$field->toRow(),
                'pid' => $formId,
                'sorting' => $sorting,
                'tstamp' => time(),
            ]);
        }
    }
}
