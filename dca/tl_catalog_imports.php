<?php

$GLOBALS['TL_DCA']['tl_catalog_imports'] = [
    'config' => [
        'dataContainer' => 'Table',
        'onload_callback' => [
            [ 'CMImporter\tl_catalog_imports', 'responseJsonByStartImport' ]
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary'
            ]
        ]
    ],
    'list' => [
        'sorting' => [
            'mode' => 2,
            'flag' => 1,
            'fields' => [ 'name' ],
            'panelLayout' => 'filter;sort,search,limit'
        ],
        'label' => [
            'showColumns' => true,
            'fields' => [ 'name', 'csvFile', 'last_import', 'state' ]
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['edit'],
                'href' => 'act=edit',
                'icon' => 'header.gif'
            ],
            'import' => [
                'label' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['import'],
                'href' => 'startImport=1',
                'icon' => 'sync.gif',
                'attributes' => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['tl_catalog_imports']['importConfirm'] ?? '') . '\'))return false;Backend.getScrollOffset()"'
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif'
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\'))return false;Backend.getScrollOffset()"'
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif'
            ]
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ]
        ]
    ],
    'palettes' => [
        '__selector__' => [ 'clearTable' ],
        'default' => '{general_settings},name,tablename,state,last_import;{csv_settings},csvFile,delimiter,useCSVHeader,mapping,clearTable;{data_type_settings},filesFolder,datimFormat;{field_settings},titleTpl,useAlias,titleField;'
    ],
    'subpalettes' => [
        'clearTable' => 'deleteQuery'
    ],
    'fields' => [
        'id' => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'name' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['name'],
            'inputType' => 'text',
            'eval' => [
                'doNotCopy' => true,
                'mandatory' => true,
                'tl_class' => 'w50',
                'maxlength' => 255
            ],
            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'tablename' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['tablename'],
            'inputType' => 'select',
            'eval' => [
                'chosen' => true,
                'maxlength' => 128,
                'doNotCopy' => true,
                'mandatory' => true,
                'tl_class' => 'w50',
                'submitOnChange' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],
            'options_callback' => [ 'CMImporter\tl_catalog_imports', 'getTables' ],
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'csvFile' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['csvFile'],
            'inputType' => 'fileTree',
            'eval' => [
                'files' => true,
                'doNotCopy' => true,
                'mandatory' => true,
                'tl_class' => 'clr',
                'fieldType' => 'radio',
                'extensions' => 'csv,txt'
            ],
            'save_callback' => [ ['CMImporter\tl_catalog_imports', 'savePath'] ],
            'load_callback' => [ ['CMImporter\tl_catalog_imports', 'convertToUUID'] ],
            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'clearTable' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['clearTable'],
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'clr',
                'submitOnChange' => true
            ],
            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],
        'state' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['state'],
            'inputType' => 'select',
            'eval' => [
                'disabled' => true,
                'tl_class' => 'w50',
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['stateMessages'],
            'options' => &$GLOBALS['CTLG_IMPORT_GLOBALS']['STATES'],
            'exclude' => true,
            'sql' => "varchar(12) NOT NULL default ''"
        ],
        'last_import' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['last_import'],
            'inputType' => 'text',
            'eval' => [
                'rgxp'=>'datim',
                'doNotCopy'=>true,
                'disabled' => true,
                'tl_class' => 'w50'
            ],
            'flag' => 6,
            'exclude' => true,
            'sorting' => true,
            'sql' => "varchar(64) NOT NULL default ''"
        ],
        'delimiter' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['delimiter'],
            'inputType' => 'text',
            'eval' => [
                'tl_class' => 'w50'
            ],
            'exclude' => true,
            'sql' => "varchar(12) NOT NULL default ''"
        ],
        'useCSVHeader' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['useCSVHeader'],
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'w50 m12',
                'submitOnChange' => true
            ],
            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],
        'mapping' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['mapping'],
            'inputType' => 'keyValueMapping',
            'eval' => [
                'tl_class' => 'clr',
                'getHeadOptions' => [ 'CMImporter\tl_catalog_imports', 'getHeadOptions' ],
                'getDataTypes' => [ 'CMImporter\tl_catalog_imports', 'getDataTypes' ],
                'getColumns' => [ 'CMImporter\tl_catalog_imports', 'getColumns' ]
            ],
            'exclude' => true,
            'sql' => "blob NULL"
        ],
        'datimFormat' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['datimFormat'],
            'inputType' => 'text',
            'eval' => [
                'decodeEntities' => true,
                'tl_class'=>'w50 clr'
            ],
            'exclude' => true,
            'sql' => "varchar(32) NOT NULL default ''"
        ],
        'filesFolder' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['filesFolder'],
            'inputType' => 'fileTree',
            'eval' => [
                'files' => false,
                'doNotCopy' => true,
                'tl_class' => 'clr',
                'fieldType' => 'radio'
            ],
            'save_callback' => [ ['CMImporter\tl_catalog_imports', 'savePath'] ],
            'load_callback' => [ ['CMImporter\tl_catalog_imports', 'convertToUUID'] ],
            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'titleTpl' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['titleTpl'],
            'inputType' => 'text',
            'eval' => [
                'tl_class' => 'w50'
            ],
            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'titleField' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['titleField'],
            'inputType' => 'text',
            'eval' => [
                'tl_class' => 'w50'
            ],
            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'useAlias' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['useAlias'],
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'w50 m12'
            ],
            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],
        'deleteQuery' => [
            'label' => &$GLOBALS['TL_LANG']['tl_catalog_imports']['deleteQuery'],
            'inputType' => 'catalogTaxonomyWizard',
            'eval' => [
                'dcTable' => 'tl_catalog_imports',
                'taxonomyTable' => [ 'CMImporter\tl_catalog_imports', 'getQueryTable' ],
                'taxonomyEntities' => [ 'CMImporter\tl_catalog_imports', 'getQueryFields' ]
            ],
            'exclude' => true,
            'sql' => "blob NULL"
        ]
    ]
];