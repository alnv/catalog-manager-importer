<?php

array_insert( $GLOBALS['TL_DCA']['tl_catalog']['list']['global_operations'], 0, [

    'csvImporter' => [

        'label' => &$GLOBALS['TL_LANG']['tl_catalog']['csvImporter'],
        'href' => 'table=tl_catalog_imports',
        'icon' => 'edit.gif'
    ]
]);