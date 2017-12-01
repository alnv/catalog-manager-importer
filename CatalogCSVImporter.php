<?php

namespace CMImporter;

class CatalogCSVImporter {


    protected $strDelimiter;
    protected $objFile = null;


    public function __construct( $strCSVFilePath, $strDelimiter = '' ) {

        $this->strDelimiter = $strDelimiter ? $strDelimiter : ',';
        ini_set( 'auto_detect_line_endings', true );
        $this->objFile = fopen( TL_ROOT . '/' . $strCSVFilePath, 'r' );
    }


    public function readAndGetCSVHeader( $blnKeysOnly = false ) {

        $arrData = fgetcsv( $this->objFile, 0, $this->strDelimiter );

        if ( !is_array( $arrData ) || empty( $arrData ) ) return [];
        if ( $blnKeysOnly ) return $arrData;

        return array_keys( $arrData );
    }


    public function close() {

        fclose( $this->objFile );
        ini_set( 'auto_detect_line_endings', false );
    }
}