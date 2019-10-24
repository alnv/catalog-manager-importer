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

        if ( Toolkit::isEmpty( $strValue ) || !is_string( $strValue ) ) {

            return '';
        }

        if ( !Toolkit::isEmpty( $strValue ) ) {

            return \FilesModel::findByPath( $strValue )->uuid;
        }

        return '';
    }


    public function getHeadOptions() {

        $arrReturn = [];
        $objImporter = $this->Database->prepare('SELECT * FROM tl_catalog_imports WHERE id = ?')->limit(1)->execute( \Input::get('id') );

        if ( !$objImporter->csvFile ) return $arrReturn;

        $objCSVImporter = new CatalogCSVImporter( $objImporter->csvFile, $objImporter->tablename, $objImporter->delimiter );
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

            $strLabel = '';

            if ( isset( $arrField['_dcFormat']['label'] ) && $arrField['_dcFormat']['label'][0] ) {

                $strLabel = $arrField['_dcFormat']['label'][0];
            }

            if ( !$strLabel ) {

                $strLabel = $strFieldname;
            }

            $arrReturn[ $strFieldname ] = $strLabel;
        }

        return $arrReturn;
    }


    public function responseJsonByStartImport() {

        if ( !\Input::get('startImport') ) return null;

        $objImporter = $this->Database->prepare('SELECT * FROM tl_catalog_imports WHERE id = ?')->execute( \Input::get('id') );
        $arrMapping = Toolkit::deserialize( $objImporter->mapping );
        $strCsvFile = TL_ROOT . '/' . $objImporter->csvFile;

        $arrDataTypeSettings = [

            'titleField' => $objImporter->titleField ?: 'title',
            'useAlias' => $objImporter->useAlias ? true : false,
            'clearTable' => $objImporter->clearTable ? true : false,
            'datimFormat' => $objImporter->datimFormat ?: \Config::get('datimFormat'),
            'titleTpl' => \StringUtil::decodeEntities( $objImporter->titleTpl ) ?: '',
            'deleteQuery' => deserialize( $objImporter->deleteQuery, true ),
            'filesFolder' => TL_ROOT . '/'. $objImporter->filesFolder ?: TL_ROOT . '/'. 'files'
        ];

        if ( !file_exists( $strCsvFile ) || !$this->Database->tableExists( $objImporter->tablename ) ) $this->sendResponse( '500' );

        $objCsvImporter = new CatalogCSVImporter( $objImporter->csvFile, $objImporter->tablename, $objImporter->delimiter );
        $objCsvImporter->prepareData( $arrMapping, $arrDataTypeSettings, ( $objImporter->useCSVHeader ? true : false ) );
        $objCsvImporter->saveCsvToDatabase( $objImporter->tablename, $arrDataTypeSettings );
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


    public function getQueryTable( \DataContainer $dc ) {

        $strTable = $dc->activeRecord->tablename ? $dc->activeRecord->tablename : '';

        if ( !$strTable || !$this->Database->tableExists( $strTable ) ) {

            return '';
        }

        return $strTable;
    }


    public function getQueryFields( \DataContainer $dc, $strTablename, $arrForbiddenTypes = null ) {

        $arrReturn = [];

        if ( !$strTablename ) return $arrReturn;
        if ( !$this->Database->tableExists( $strTablename ) ) return $arrReturn;

        if ( is_null( $arrForbiddenTypes ) || !is_array( $arrForbiddenTypes ) ) {

            $arrForbiddenTypes = [ 'upload' ];
        }

        $objCatalogFieldBuilder = new CatalogFieldBuilder();
        $objCatalogFieldBuilder->initialize( $strTablename );
        $arrFields = $objCatalogFieldBuilder->getCatalogFields( true, null );

        foreach ( $arrFields as $strFieldname => $arrField ) {

            if ( !$this->Database->fieldExists( $strFieldname, $strTablename ) ) continue;
            if ( in_array( $arrField['type'], Toolkit::excludeFromDc() ) ) continue;
            if ( $arrField['type'] == 'textarea' && $arrField['rte'] ) continue;
            if ( in_array( $arrField['type'], $arrForbiddenTypes ) ) continue;

            $arrReturn[ $strFieldname ] = $arrField['_dcFormat'];
        }

        return $arrReturn;
    }
}