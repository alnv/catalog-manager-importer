<?php

use Contao\DC_Table;
use Alnv\CatalogManagerImporterBundle\Classes\tl_catalog_imports;

$GLOBALS['TL_DCA']['tl_catalog_imports'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'onload_callback' => [
            [tl_catalog_imports::class, 'responseJsonByStartImport']
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
            'fields' => ['name'],
            'panelLayout' => 'filter;sort,search,limit'
        ],
        'label' => [
            'showColumns' => true,
            'fields' => ['name', 'csvFile', 'last_import', 'state']
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.svg'
            ],
            'import' => [
                'href' => 'startImport=1',
                'icon' => 'sync.svg',
                'attributes' => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['tl_catalog_imports']['importConfirm'] ?? '') . '\'))return false;Backend.getScrollOffset()"'
            ],
            'copy' => [
                'href' => 'act=copy',
                'icon' => 'copy.svg'
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\'))return false;Backend.getScrollOffset()"'
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg'
            ]
        ],
        'global_operations' => [
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ]
        ]
    ],
    'palettes' => [
        '__selector__' => ['clearTable'],
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
            'options_callback' => [tl_catalog_imports::class, 'getTables'],
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],
        'csvFile' => [
            'inputType' => 'fileTree',
            'eval' => [
                'files' => true,
                'doNotCopy' => true,
                'mandatory' => true,
                'tl_class' => 'clr',
                'fieldType' => 'radio',
                'extensions' => 'csv,txt'
            ],
            'save_callback' => [[tl_catalog_imports::class, 'savePath']],
            'load_callback' => [[tl_catalog_imports::class, 'convertToUUID']],
            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'clearTable' => [
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'clr',
                'submitOnChange' => true
            ],
            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],
        'state' => [
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
            'inputType' => 'text',
            'eval' => [
                'rgxp' => 'datim',
                'doNotCopy' => true,
                'disabled' => true,
                'tl_class' => 'w50'
            ],
            'flag' => 6,
            'exclude' => true,
            'sorting' => true,
            'sql' => "varchar(64) NOT NULL default ''"
        ],
        'delimiter' => [
            'inputType' => 'text',
            'eval' => [
                'tl_class' => 'w50'
            ],
            'exclude' => true,
            'sql' => "varchar(12) NOT NULL default ''"
        ],
        'useCSVHeader' => [
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'w50 m12',
                'submitOnChange' => true
            ],
            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],
        'mapping' => [
            'inputType' => 'keyValueMapping',
            'eval' => [
                'tl_class' => 'clr',
                'getHeadOptions' => [tl_catalog_imports::class, 'getHeadOptions'],
                'getDataTypes' => [tl_catalog_imports::class, 'getDataTypes'],
                'getColumns' => [tl_catalog_imports::class, 'getColumns']
            ],
            'exclude' => true,
            'sql' => "blob NULL"
        ],
        'datimFormat' => [
            'inputType' => 'text',
            'eval' => [
                'decodeEntities' => true,
                'tl_class' => 'w50 clr'
            ],
            'exclude' => true,
            'sql' => "varchar(32) NOT NULL default ''"
        ],
        'filesFolder' => [
            'inputType' => 'fileTree',
            'eval' => [
                'files' => false,
                'doNotCopy' => true,
                'tl_class' => 'clr',
                'fieldType' => 'radio'
            ],
            'save_callback' => [[tl_catalog_imports::class, 'savePath']],
            'load_callback' => [[tl_catalog_imports::class, 'convertToUUID']],
            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'titleTpl' => [
            'inputType' => 'text',
            'eval' => [
                'tl_class' => 'w50'
            ],
            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'titleField' => [
            'inputType' => 'text',
            'eval' => [
                'tl_class' => 'w50'
            ],
            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'useAlias' => [
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'w50 m12'
            ],
            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],
        'deleteQuery' => [
            'inputType' => 'catalogTaxonomyWizard',
            'eval' => [
                'dcTable' => 'tl_catalog_imports',
                'taxonomyTable' => [tl_catalog_imports::class, 'getQueryTable'],
                'taxonomyEntities' => [tl_catalog_imports::class, 'getQueryFields']
            ],
            'exclude' => true,
            'sql' => "blob NULL"
        ]
    ]
];