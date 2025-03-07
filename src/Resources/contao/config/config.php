<?php

use Contao\System;
use Symfony\Component\HttpFoundation\Request;
use Alnv\CatalogManagerImporterBundle\KeyValueMappingWizard;

$GLOBALS['BE_MOD']['catalog-manager-extensions']['catalog-manager-importer'] = [
    'name' => 'catalog-manager-importer',
    'tables' => [
        'tl_catalog_imports'
    ]
];

$GLOBALS['BE_FFL']['keyValueMapping'] = KeyValueMappingWizard::class;

if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
    $GLOBALS['TL_CSS']['catalogManagerImporterKeyValueMappingWizard'] = 'bundles/alnvcatalogmanagerimporter/key_value_mapping_wizard.css';
}

$GLOBALS['CTLG_IMPORT_GLOBALS'] = [];
$GLOBALS['CTLG_IMPORT_GLOBALS']['DATA_TYPES'] = [
    'TEXT_UTF8',
    'TEXT',
    'DATE',
    'FILE',
    'INT'
];
$GLOBALS['CTLG_IMPORT_GLOBALS']['STATES'] = [
    '200',
    '500'
];