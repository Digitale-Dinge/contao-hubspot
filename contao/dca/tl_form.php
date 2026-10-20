<?php

declare(strict_types=1);

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_form']['palettes']['__selector__'][] = 'dd_hubspot';
$GLOBALS['TL_DCA']['tl_form']['subpalettes']['dd_hubspot'] = 'dd_hubspot_portal_id,dd_hubspot_form_id';

$GLOBALS['TL_DCA']['tl_form']['fields']['dd_hubspot'] = [
    'inputType' => 'checkbox',
    'exclude' => true,
    'filter' => true,
    'eval' => [
        'submitOnChange' => true,
        'tl_class' => 'clr',
    ],
    'sql' => [
        'type' => Types::BOOLEAN,
        'default' => false,
    ],
];

$GLOBALS['TL_DCA']['tl_form']['fields']['dd_hubspot_portal_id'] = [
    'inputType' => 'text',
    'exclude' => true,
    'search' => true,
    'eval' => [
        'mandatory' => true,
        'rgxp' => 'natural',
        'maxlength' => 32,
        'tl_class' => 'w50',
    ],
    'sql' => [
        'type' => Types::STRING,
        'length' => 32,
        'default' => '',
    ],
];

$GLOBALS['TL_DCA']['tl_form']['fields']['dd_hubspot_form_id'] = [
    'inputType' => 'text',
    'exclude' => true,
    'search' => true,
    'eval' => [
        'mandatory' => true,
        'rgxp' => 'alias',
        'maxlength' => 64,
        'tl_class' => 'w50',
    ],
    'sql' => [
        'type' => Types::STRING,
        'length' => 64,
        'default' => '',
    ],
];

PaletteManipulator::create()
    ->addField('dd_hubspot', 'storeSession', PaletteManipulator::POSITION_AFTER, 'config_legend')
    ->applyToPalette('default', 'tl_form')
;

$GLOBALS['TL_DCA']['tl_form']['list']['operations']['dd_hubspot_import'] = [
    'route' => 'dd_hubspot_import',
    'icon' => 'theme_import.svg',
];
