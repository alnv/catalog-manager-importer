<?php

$GLOBALS['TL_DCA']['tl_catalog_importer'] = [

    'config' => [

        'dataContainer' => 'Table',

        'onload_callback' => [

            [ 'CMImporter\tl_catalog_importer', 'responseJsonByStartImport' ]
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
            'fields' => [ 'name', 'csvFile', 'lastState' ]
        ],

        'operations' => [

            'edit' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_importer']['edit'],
                'href' => 'act=edit',
                'icon' => 'header.gif'
            ],

            'import' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_importer']['import'],
                'href' => 'startImport=1',
                'icon' => 'sync.gif'
            ],

            'copy' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_importer']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif'
            ],

            'delete' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_importer']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ],

            'show' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_importer']['show'],
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

        'default' => '{general_settings},name,tablename,lastState;{csv_settings},csvFile,delimiter,clearTable,useCSVHeader,mapping'
    ],

    'fields' => [

        'id' => [

            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],

        'tstamp' => [

            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],

        'name' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_importer']['name'],
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

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_importer']['tablename'],
            'inputType' => 'select',

            'eval' => [

                'maxlength' => 128,
                'doNotCopy' => true,
                'mandatory' => true,
                'tl_class' => 'w50',
                'submitOnChange' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true
            ],

            'options_callback' => [ 'CMImporter\tl_catalog_importer', 'getTables' ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'csvFile' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_importer']['csvFile'],
            'inputType' => 'fileTree',

            'eval' => [

                'files' => true,
                'tl_class' => 'clr',
                'doNotCopy' => true,
                'mandatory' => true,
                'fieldType' => 'radio',
                'extensions' => 'csv,txt'
            ],

            'save_callback' => [ ['CMImporter\tl_catalog_importer', 'savePath'] ],
            'load_callback' => [ ['CMImporter\tl_catalog_importer', 'convertToUUID'] ],

            'exclude' => true,
            'sql' => "varchar(255) NOT NULL default ''"
        ],

        'clearTable' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_importer']['clearTable'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'clr'
            ],
            
            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'lastState' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_importer']['lastState'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'w50'
            ],

            'exclude' => true,
            'sql' => "varchar(64) NOT NULL default ''"
        ],

        'delimiter' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_importer']['delimiter'],
            'inputType' => 'text',

            'eval' => [

                'tl_class' => 'clr w50'
            ],

            'exclude' => true,
            'sql' => "varchar(12) NOT NULL default ''"
        ],

        'useCSVHeader' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_importer']['useCSVHeader'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'clr',
                'submitOnChange' => true
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'mapping' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_importer']['mapping'],
            'inputType' => 'keyValueMapping',

            'eval' => [

                'tl_class' => 'clr',
                'getHeadOptions' => [ 'CMImporter\tl_catalog_importer', 'getHeadOptions' ],
                'getDataTypes' => [ 'CMImporter\tl_catalog_importer', 'getDataTypes' ],
                'getColumns' => [ 'CMImporter\tl_catalog_importer', 'getColumns' ]
            ],

            'exclude' => true,
            'sql' => "blob NULL"
        ]
    ]
];