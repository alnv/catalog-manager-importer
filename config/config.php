<?php

$GLOBALS['BE_MOD']['system']['catalog-manager']['tables'][] = 'tl_catalog_importer';
$GLOBALS['BE_FFL']['keyValueMapping'] = 'CMImporter\KeyValueMappingWizard';

if ( TL_MODE == 'BE' ) {

    $GLOBALS['TL_CSS']['catalogManagerImporterKeyValueMappingWizard'] = $GLOBALS['TL_CONFIG']['debugMode']
        ? 'system/modules/catalog-manager-importer/assets/key_value_mapping_wizard.css'
        : 'system/modules/catalog-manager-importer/assets/key_value_mapping_wizard.css';
}