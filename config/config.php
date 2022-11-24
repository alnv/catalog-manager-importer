<?php

$GLOBALS['BE_MOD']['catalog-manager-extensions']['catalog-manager-importer'] = [

    'name' => 'catalog-manager-importer',
    'icon' => 'system/modules/catalog-manager-importer/assets/icon.svg',

    'tables' => [

        'tl_catalog_imports'
    ]
];

$GLOBALS['BE_FFL']['keyValueMapping'] = 'CMImporter\KeyValueMappingWizard';

if ( TL_MODE == 'BE' ) {

    $GLOBALS['TL_CSS']['catalogManagerImporterKeyValueMappingWizard'] = $GLOBALS['TL_CONFIG']['debugMode']
        ? 'system/modules/catalog-manager-importer/assets/key_value_mapping_wizard.css'
        : 'system/modules/catalog-manager-importer/assets/key_value_mapping_wizard.css';
}

$GLOBALS['CTLG_IMPORT_GLOBALS'] = [];

$GLOBALS['CTLG_IMPORT_GLOBALS']['DATA_TYPES'] = [

    'TEXT_UTF8',
    'TEXT',
    'DATE',
    'FILE',
    'INT',
    'TEXT_UTF8_ENCODE'
];

$GLOBALS['CTLG_IMPORT_GLOBALS']['STATES'] = [

    '200',
    '500'
];
