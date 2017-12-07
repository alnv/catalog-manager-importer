<?php

namespace CMImporter;

use CatalogManager\Toolkit as Toolkit;
use CatalogManager\DcCallbacks as DcCallbacks;
use CatalogManager\CatalogController as CatalogController;

class CatalogCSVImporter extends CatalogController {


    protected $arrData = [];
    protected $strDelimiter;
    protected $objFile = null;
    protected $strTablename = '';


    public function __construct( $strCSVFilePath, $strTablename, $strDelimiter = '' ) {

        ini_set( 'auto_detect_line_endings', true );

        $this->import( 'Database' );

        $this->strTablename = $strTablename;
        $this->strDelimiter = $strDelimiter ? $strDelimiter : ',';
        $this->objFile = fopen( TL_ROOT . '/' . $strCSVFilePath, 'r' );
    }


    public function readAndGetCSVHeader( $blnKeysOnly = false ) {

        $arrData = fgetcsv( $this->objFile, 0, $this->strDelimiter );

        if ( !is_array( $arrData ) || empty( $arrData ) ) return [];
        if ( $blnKeysOnly ) return $arrData;

        return array_keys( $arrData );
    }


    public function prepareData( $arrMapping, $arrDataTypeSettings, $blnIgnoreHeader = false ) {

        if ( !is_array( $arrMapping ) || empty( $arrMapping ) ) return null;

        $arrPosition = 0;
        $objCallback = new DcCallbacks();

        while ( ( $arrData = fgetcsv( $this->objFile, 0, $this->strDelimiter ) ) !== FALSE ) {

            if ( $blnIgnoreHeader ) {

                $blnIgnoreHeader = false;
                continue;
            };

            $this->arrData[ $arrPosition ] = [];

            foreach ( $arrData as $intIndex => $strValue ) {

                $arrMap = $arrMapping[ $intIndex ];
                if ( isset( $arrMap['continue'] ) && $arrMap['continue'] ) continue;

                $strFieldname = $arrMap['column'] ? $arrMap['column'] : $arrMap['head'];
                $this->arrData[ $arrPosition ][ $strFieldname ] = $this->parseValue( $strValue, $arrMap['type'], $arrDataTypeSettings );
            }

            $arrPosition++;
        }

        for ( $intIndex = 0; count( $this->arrData ) > $intIndex; $intIndex++ ) {

            if ( !Toolkit::isEmpty( $arrDataTypeSettings['titleTpl'] ) ) {

                $this->arrData[ $intIndex ]['title'] = \StringUtil::parseSimpleTokens( $arrDataTypeSettings['titleTpl'], $this->arrData[ $intIndex ]  );
            }

            if ( !Toolkit::isEmpty( $this->arrData[ $intIndex ]['title'] ) && $arrDataTypeSettings['useAlias'] ) {

                $this->arrData[ $intIndex ]['alias'] = $objCallback->generateFEAlias( '', $this->arrData[ $intIndex ]['title'], $this->strTablename, '', null );
            }
        }
    }


    public function saveCsvToDatabase( $strTable, $blnClearTable = false ) {

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


    protected function parseValue( $strValue, $strType, $arrDataTypeSettings ) {

        $strType = $GLOBALS['CTLG_IMPORT_GLOBALS']['DATA_TYPES'][ $strType ];

        switch ( $strType ) {

            case 'TEXT':

                if ( Toolkit::isEmpty( $strValue ) ) return '';

                return utf8_encode( $strValue );

                break;

            case 'FILE':

                if ( Toolkit::isEmpty( $strValue ) ) return '';

                $strPath = $arrDataTypeSettings['filesFolder'] . '/' . $strValue;

                if ( file_exists( $strPath ) ) {

                    $objFile = \FilesModel::findByPath( $strPath );

                    if ( $objFile === null ) {

                        \System::log( sprintf( 'File "%s" do not exist in tl_files table', $strPath ), __METHOD__, TL_GENERAL );

                        return '';
                    }

                    return $objFile->uuid;
                }

                \System::log( sprintf( 'File "%s" do not exist', $strPath ), __METHOD__, TL_GENERAL );

                return '';

                break;

            case 'DATE':

                if ( Toolkit::isEmpty( $strValue ) ) return '';

                try {

                    $objDate = new \Date( $strValue, $arrDataTypeSettings['datimFormat'] );

                    return $objDate->tstamp;
                }

                catch ( \Exception $objError ) {

                    \System::log( $objError->getMessage(), __METHOD__, TL_GENERAL );
                }

                return '';

                break;
        }

        return '';
    }
}