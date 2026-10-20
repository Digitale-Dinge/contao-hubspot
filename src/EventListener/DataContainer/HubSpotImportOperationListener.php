<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\EventListener\DataContainer;

use Contao\CoreBundle\DataContainer\DataContainerOperation;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Doctrine\DBAL\Connection;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HubSpotImportOperationListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[AsCallback('tl_form', 'list.operations.dd_hubspot_import.button')]
    public function onFormOperation(DataContainerOperation $operation): void
    {
        if (empty($operation->getRecord()['dd_hubspot'])) {
            $operation->hide();
        }
    }

    #[AsCallback('tl_form_field', 'list.global_operations.dd_hubspot_import.button')]
    public function onFormFieldGlobalOperation(DataContainerOperation $operation): void
    {
        $formId = (int) $operation->getDataContainer()->id;

        if (!$this->connection->fetchOne('SELECT dd_hubspot FROM tl_form WHERE id = ?', [$formId])) {
            $operation->hide();

            return;
        }

        $operation->setUrl($this->urlGenerator->generate('dd_hubspot_import', [
            'id' => $formId,
            'from' => 'tl_form_field',
        ]));
    }
}
