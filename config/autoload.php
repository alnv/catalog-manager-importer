<?php

ClassLoader::addNamespace( 'CMImporter' );

ClassLoader::addClasses([

    'CMImporter\CatalogCSVImporter' => 'system/modules/catalog-manager-importer/CatalogCSVImporter.php',
    'CMImporter\KeyValueMappingWizard' => 'system/modules/catalog-manager-importer/KeyValueMappingWizard.php',
    'CMImporter\tl_catalog_importer' => 'system/modules/catalog-manager-importer/classes/tl_catalog_importer.php'
]);