<?php

namespace CMImporter;

use CatalogManager\Toolkit as Toolkit;
use CatalogManager\CatalogController as CatalogController;

class CatalogCSVImporter extends CatalogController {


    protected $arrData = [];
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


    public function prepareData( $arrMapping = [], $blnIgnoreHeader = false ) {

        if ( !is_array( $arrMapping ) || empty( $arrMapping ) ) return null;

        $arrPosition = 0;

        while ( ( $arrData = fgetcsv( $this->objFile, 0, $this->strDelimiter ) ) !== FALSE ) {

            if ( $blnIgnoreHeader && $arrPosition == 0 ) {

                $arrPosition++;
                continue;
            };

            $this->arrData[ $arrPosition ] = [];

            foreach ( $arrData as $intIndex => $strValue ) {

                $arrMap = $arrMapping[ $intIndex ];
                if ( isset( $arrMap['continue'] ) && $arrMap['continue'] ) continue;

                $strFieldname = $arrMap['column'] ? $arrMap['column'] : $arrMap['head'];
                $this->arrData[ $arrPosition ][ $strFieldname ] = $this->parseValue( $strValue, $arrMap['type'] );
            }

            $arrPosition++;
        }
    }


    public function saveCsvToDatabase( $strTable, $blnClearTable = false ) {

        $this->import( 'Database' );

        if ( $blnClearTable && ( is_array( $this->arrData ) && !empty( $this->arrData ) ) ) {

            $this->Database->prepare( sprintf( 'DELETE FROM %s', $strTable ) )->execute();
        }

        foreach ( $this->arrData as $arrValue ) {

            $this->Database->prepare( 'INSERT INTO '. $strTable .' %s' )->set( $arrValue )->execute();
        }
    }


    public function close() {

        fclose( $this->objFile );
        ini_set( 'auto_detect_line_endings', false );
    }


    protected function parseValue( $strValue, $strType ) {

        switch ( $strType ) {

            case 'text':

                return $strValue;

                break;

            case 'file':

                return $strValue;

            case 'date':

                return $strValue;
        }

        return !Toolkit::isEmpty( $strValue ) ?: '';
    }
}