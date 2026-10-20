<?php

declare(strict_types=1);

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use DigitaleDinge\HubSpotBundle\HubSpot\LegalBasis;
use DigitaleDinge\HubSpotBundle\HubSpot\LegalConsent;
use DigitaleDinge\HubSpotBundle\HubSpot\ObjectType;
use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_form_field']['palettes']['__selector__'][] = 'dd_hubspot';
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['__selector__'][] = 'dd_hubspot_legal';
$GLOBALS['TL_DCA']['tl_form_field']['subpalettes']['dd_hubspot'] = 'dd_hubspot_object_type';
$GLOBALS['TL_DCA']['tl_form_field']['subpalettes']['dd_hubspot_legal_'.LegalConsent::Communication->value] = 'dd_hubspot_subscription_type_id';
$GLOBALS['TL_DCA']['tl_form_field']['subpalettes']['dd_hubspot_legal_'.LegalConsent::LegitimateInterest->value] = 'dd_hubspot_legal_basis,dd_hubspot_subscription_type_id';

$GLOBALS['TL_DCA']['tl_form_field']['list']['global_operations']['dd_hubspot_import'] = [
    'icon' => 'theme_import.svg',
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['dd_hubspot'] = [
    'inputType' => 'checkbox',
    'exclude' => true,
    'filter' => true,
    'eval' => [
        'submitOnChange' => true,
        'tl_class' => 'w50 clr',
    ],
    'sql' => [
        'type' => Types::BOOLEAN,
        'default' => false,
    ],
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['dd_hubspot_object_type'] = [
    'inputType' => 'select',
    'exclude' => true,
    'enum' => ObjectType::class,
    'eval' => [
        'tl_class' => 'w50',
    ],
    'sql' => [
        'type' => Types::STRING,
        'length' => 8,
        'default' => ObjectType::Contact->value,
    ],
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['dd_hubspot_legal'] = [
    'inputType' => 'select',
    'exclude' => true,
    'enum' => LegalConsent::class,
    'eval' => [
        'includeBlankOption' => true,
        'submitOnChange' => true,
        'tl_class' => 'w50',
    ],
    'sql' => [
        'type' => Types::STRING,
        'length' => 32,
        'default' => '',
    ],
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['dd_hubspot_legal_basis'] = [
    'inputType' => 'select',
    'exclude' => true,
    'enum' => LegalBasis::class,
    'eval' => [
        'tl_class' => 'w50 clr',
    ],
    'sql' => [
        'type' => Types::STRING,
        'length' => 8,
        'default' => LegalBasis::Lead->value,
    ],
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['dd_hubspot_subscription_type_id'] = [
    'inputType' => 'text',
    'exclude' => true,
    'eval' => [
        'mandatory' => true,
        'rgxp' => 'natural',
        'tl_class' => 'w50',
    ],
    'sql' => [
        'type' => Types::BIGINT,
        'unsigned' => true,
        'default' => 0,
    ],
];

PaletteManipulator::create()
    ->addLegend('hubspot_legend', 'fconfig_legend', PaletteManipulator::POSITION_AFTER)
    ->addField('dd_hubspot', 'hubspot_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('checkbox', 'tl_form_field')
    ->applyToPalette('text', 'tl_form_field')
    ->applyToPalette('textdigit', 'tl_form_field')
    ->applyToPalette('textcustom', 'tl_form_field')
    ->applyToPalette('textarea', 'tl_form_field')
    ->applyToPalette('textareacustom', 'tl_form_field')
    ->applyToPalette('select', 'tl_form_field')
    ->applyToPalette('radio', 'tl_form_field')
    ->applyToPalette('range', 'tl_form_field')
    ->applyToPalette('hidden', 'tl_form_field')
    ->applyToPalette('hiddencustom', 'tl_form_field')
;

PaletteManipulator::create()
    ->addLegend('hubspot_legend', 'type_legend', PaletteManipulator::POSITION_AFTER)
    ->addField('dd_hubspot', 'hubspot_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('explanation', 'tl_form_field')
;
