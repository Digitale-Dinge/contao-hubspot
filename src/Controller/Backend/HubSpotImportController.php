<?php

declare(strict_types=1);

namespace DigitaleDinge\HubSpotBundle\Controller\Backend;

use Contao\CoreBundle\Controller\Backend\AbstractBackendController;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\CreateAction;
use DigitaleDinge\HubSpotBundle\Import\FormDefinition;
use DigitaleDinge\HubSpotBundle\Import\FormDefinitionLoader;
use DigitaleDinge\HubSpotBundle\Import\FormFieldImporter;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('%contao.backend.route_prefix%/hubspot/import/{id}', name: 'dd_hubspot_import', requirements: [
    'id' => '\d+',
], defaults: [
    '_scope' => 'backend',
], methods: ['GET', 'POST'])]
class HubSpotImportController extends AbstractBackendController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly FormDefinitionLoader $loader,
        private readonly FormFieldImporter $importer,
    ) {
    }

    public function __invoke(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, 'form');

        $form = $this->connection->fetchAssociative('SELECT * FROM tl_form WHERE id = ?', [$id]);

        if (!$form) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(ContaoCorePermissions::DC_PREFIX.'tl_form_field', new CreateAction('tl_form_field', [
            'pid' => $id,
        ]));

        $portalId = $form['dd_hubspot_portal_id'];
        $formId = $form['dd_hubspot_form_id'];
        $definition = \is_string($portalId) && \is_string($formId) ? $this->loader->load($portalId, $formId) : null;
        $fieldsUrl = $this->generateUrl('contao_backend', [
            'do' => 'form',
            'table' => 'tl_form_field',
            'id' => $id,
        ]);

        if ($definition instanceof FormDefinition && $request->isMethod('POST')) {
            $this->importer->import($id, $definition->fields);

            return $this->redirect($fieldsUrl);
        }

        return $this->render('@Contao/backend/hubspot_import.html.twig', [
            'form' => $form,
            'definition' => $definition,
            'has_fields' => (bool) $this->connection->fetchOne('SELECT COUNT(*) FROM tl_form_field WHERE pid = ?', [$id]),
            'back_url' => 'tl_form_field' === $request->query->get('from') ? $fieldsUrl : $this->generateUrl('contao_backend', [
                'do' => 'form',
            ]),
            'fields_url' => $fieldsUrl,
        ]);
    }
}
