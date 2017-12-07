<?php

namespace CMImporter;

use CatalogManager\Toolkit as Toolkit;
use CatalogManager\CatalogFieldBuilder as CatalogFieldBuilder;

class tl_catalog_imports extends \Backend {


    public function savePath( $strValue ) {

        if ( Toolkit::isEmpty( $strValue ) ) {

            return '';
        }

        if ( !Toolkit::isEmpty( $strValue ) ) {

            return \FilesModel::findByPk( $strValue )->path;
        }

        throw new \Exception( 'this file do not exist in tl_files table.' );
    }


    public function convertToUUID( $strValue ) {

        if ( Toolkit::isEmpty( $strValue ) ) {

            return '';
        }

        if ( !Toolkit::isEmpty( $strValue ) ) {

            return \FilesModel::findByPath($strValue)->uuid;
        }

        return '';
    }


    public function getHeadOptions() {

        $arrReturn = [];
        $objImporter = $this->Database->prepare('SELECT * FROM tl_catalog_imports WHERE id = ?')->limit(1)->execute( \Input::get('id') );

        if ( !$objImporter->csvFile ) return $arrReturn;

        $objCSVImporter = new CatalogCSVImporter( $objImporter->csvFile, $objImporter->delimiter );
        $arrReturn = $objCSVImporter->readAndGetCSVHeader( $objImporter->useCSVHeader ? true : false );

        $objCSVImporter->close();

        return $arrReturn;
    }


    public function getDataTypes() {

        return $GLOBALS['CTLG_IMPORT_GLOBALS']['DATA_TYPES'];
    }


    public function getTables() {

        $arrReturn = [];
        $objModules = $this->Database->prepare('SELECT * FROM tl_catalog WHERE tstamp > 0')->execute( \Input::get('id') );

        if ( !$objModules->numRows ) return $arrReturn;

        while ( $objModules->next() ) {

            if ( !$objModules->tablename ) continue;

            $arrReturn[ $objModules->tablename ] = $objModules->name ? $objModules->name : $objModules->tablename;
        }

        return $arrReturn;
    }


    public function getColumns() {

        $arrReturn = [];
        $objImporter = $this->Database->prepare('SELECT * FROM tl_catalog_imports WHERE id = ?')->limit(1)->execute( \Input::get('id') );

        if ( !$objImporter->tablename ) return [];

        $objFieldBuilder = new CatalogFieldBuilder();
        $objFieldBuilder->initialize( $objImporter->tablename );
        $arrFields = $objFieldBuilder->getCatalogFields( true, null, false, false );

        foreach ( $arrFields as $strFieldname => $arrField ) {

            $arrReturn[ $strFieldname ] = $arrField['_dcFormat']['label'][0];
        }

        return $arrReturn;
    }


    public function responseJsonByStartImport() {

        if ( !\Input::get('startImport') ) return null;

        $objImporter = $this->Database->prepare('SELECT * FROM tl_catalog_imports WHERE id = ?')->execute( \Input::get('id') );
        $arrMapping = Toolkit::deserialize( $objImporter->mapping );
        $strCsvFile = TL_ROOT . '/' . $objImporter->csvFile;

        $arrDataTypeSettings = [

            'datimFormat' => $objImporter->datimFormat ?: \Config::get('datimFormat'),
            'filesFolder' => TL_ROOT . '/'. $objImporter->filesFolder ?: TL_ROOT . '/'. 'files'
        ];

        if ( !file_exists( $strCsvFile ) ) $this->sendResponse( '500' );

        $objCsvImporter = new CatalogCSVImporter( $objImporter->csvFile, $objImporter->delimiter );
        $objCsvImporter->prepareData( $arrMapping, $arrDataTypeSettings, ( $objImporter->useCSVHeader ? true : false ) );
        $objCsvImporter->saveCsvToDatabase( $objImporter->tablename, ( $objImporter->clearTable ? true : false ) );
        $objCsvImporter->close();

        $this->sendResponse( '200' );
    }


    protected function sendResponse( $strState ) {
        
        $this->Database->prepare( 'UPDATE tl_catalog_imports %s WHERE id=?' )->set([

            'last_import' => time(),
            'state' => $strState,

        ])->execute( \Input::get('id') );

        $this->redirect( preg_replace( '/&(amp;)?startImport=[^&]*/i', '', preg_replace( '/&(amp;)?' . preg_quote( "1", '/' ) . '=[^&]*/i', '', \Environment::get('request') ) ) );
    }
}