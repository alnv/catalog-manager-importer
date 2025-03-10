<?php

namespace Alnv\CatalogManagerImporterBundle;

use Alnv\CatalogManagerBundle\Toolkit;
use Alnv\CatalogManagerBundle\SQLQueryBuilder;
use Alnv\CatalogManagerBundle\CatalogController;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\FilesModel;
use Contao\Database;
use Contao\System;
use Contao\Date;
use Psr\Log\LogLevel;

class CatalogCSVImporter extends CatalogController
{

    protected array $arrData = [];

    protected string $strDelimiter;

    protected $objFile;

    protected string $strTablename = '';

    public function __construct($strCSVFilePath, $strTablename, $strDelimiter = '')
    {

        \ini_set('auto_detect_line_endings', true);

        $this->strTablename = $strTablename;
        $this->strDelimiter = $strDelimiter ?: ',';
        $strRootDir = System::getContainer()->getParameter('kernel.project_dir');
        $this->objFile = \fopen($strRootDir . '/' . $strCSVFilePath, 'r');

        parent::__construct();
    }


    public function readAndGetCSVHeader($blnKeysOnly = false): array
    {

        $arrData = \fgetcsv($this->objFile, 0, $this->strDelimiter);

        if (!\is_array($arrData) || empty($arrData)) return [];
        if ($blnKeysOnly) return $arrData;

        return \array_keys($arrData);
    }

    public function prepareData($arrMapping, $arrOptions, $blnIgnoreHeader = false)
    {

        if (!\is_array($arrMapping) || empty($arrMapping)) return null;

        $arrPosition = 0;

        if ($arrOptions['clearTable']) {

            $strQuery = '';
            $arrValues = [];

            if (\is_array($arrOptions['deleteQuery']) && !empty($arrOptions['deleteQuery'])) {

                $arrQuery = [
                    'table' => $this->strTablename,
                    'where' => Toolkit::parseQueries($arrOptions['deleteQuery']['query'])
                ];

                $objSQLQueryBuilder = new SQLQueryBuilder();
                $strQuery = $objSQLQueryBuilder->getWhereQuery($arrQuery);
                $arrValues = $objSQLQueryBuilder->getValues();
            }

            Database::getInstance()->prepare(sprintf('DELETE FROM %s' . $strQuery, $this->strTablename))->execute($arrValues);
        }

        while (($arrData = fgetcsv($this->objFile, 0, $this->strDelimiter)) !== FALSE) {

            if ($blnIgnoreHeader) {

                $blnIgnoreHeader = false;

                continue;
            };

            $this->arrData[$arrPosition] = [];

            foreach ($arrData as $intIndex => $strValue) {

                $arrMap = $arrMapping[$intIndex];

                if (isset($arrMap['continue']) && $arrMap['continue']) continue;

                $strFieldname = $arrMap['column'] ?: $arrMap['head'];
                $this->arrData[$arrPosition][$strFieldname] = $this->parseValue($strValue, $arrMap['type'], $arrOptions);
            }

            $arrPosition++;
        }

        for ($intIndex = 0; \count($this->arrData) > $intIndex; $intIndex++) {

            if (!Toolkit::isEmpty($arrOptions['titleTpl'])) {

                $this->arrData[$intIndex]['title'] = System::getContainer()->get('contao.string.simple_token_parser')->parse($arrOptions['titleTpl'], $this->arrData[$intIndex], true);
            }

            $this->arrData[$intIndex]['tstamp'] = time();
        }
    }

    protected function generateAlias($varValue, $varValues = [])
    {

        if ($varValue === '' || $varValue === null) {
            return \md5(\time() . \uniqid());
        }

        $varValue = Toolkit::slug($varValue);
        $objEntity = Database::getInstance()->prepare('SELECT * FROM ' . $this->strTablename . ' WHERE `alias` = ?')->execute($varValue);

        if ($objEntity->numRows) {
            $varValue .= isset($varValues['id']) ? '_' . $varValues['id'] : '_' . uniqid();
        }

        return $varValue;
    }

    public function saveCsvToDatabase($strTable, $arrOptions): void
    {

        foreach ($this->arrData as $arrValue) {

            if ($arrOptions['useAlias']) {
                $arrValue['alias'] = $this->generateAlias($arrValue[$arrOptions['titleField']], $arrValue);
            }

            if (isset($GLOBALS['TL_HOOKS']['catalogImporterBeforeSave']) && \is_array($GLOBALS['TL_HOOKS']['catalogImporterBeforeSave'])) {
                foreach ($GLOBALS['TL_HOOKS']['catalogImporterBeforeSave'] as $callback) {
                    System::importStatic($callback[0])->{$callback[1]}($arrValue, $arrOptions, $this->strTablename, $this);
                }
            }

            if (\is_array($arrValue) && !empty($arrValue)) {
                Database::getInstance()->prepare('INSERT INTO ' . $strTable . ' %s')->set($arrValue)->execute();
            }
        }
    }

    public function close(): void
    {

        \fclose($this->objFile);
        \ini_set('auto_detect_line_endings', false);
    }

    protected function parseValue($strValue, $strType, $arrOptions)
    {

        $strType = $GLOBALS['CTLG_IMPORT_GLOBALS']['DATA_TYPES'][$strType] ?? '';

        switch ($strType) {

            case 'TEXT':

                if (Toolkit::isEmpty($strValue)) return '';

                return $strValue;

            case 'TEXT_UTF8':

                if (Toolkit::isEmpty($strValue)) return '';

                return \mb_convert_encoding($strValue, 'UTF-8');

            case 'INT':

                if (Toolkit::isEmpty($strValue)) return 0;

                return \intval($strValue);

            case 'FILE':

                if (Toolkit::isEmpty($strValue)) {
                    return '';
                }

                $arrValues = [];
                $strRootDir = System::getContainer()->getParameter('kernel.project_dir');
                $varPaths = \explode(',', $strValue);

                if (\is_array($varPaths) && !empty($varPaths)) {

                    foreach ($varPaths as $strPath) {

                        if ($arrOptions['filesFolder']) {
                            $strPath = $arrOptions['filesFolder'] . '/' . $strPath;
                        } else {
                            $strPath = $strRootDir . ($strPath[0] == '/' ? $strPath : '/' . $strPath);
                        }

                        $strPath = \strval(\str_replace(' ', '', $strPath));

                        if (\file_exists($strPath)) {

                            $objFile = FilesModel::findByPath($strPath);

                            if (!$objFile) {

                                System::getContainer()
                                    ->get('monolog.logger.contao')
                                    ->log(LogLevel::ERROR, \sprintf('File "%s" do not exist in tl_files table', $strPath), ['contao' => new ContaoContext(__CLASS__ . '::' . __FUNCTION__)]);

                                continue;
                            }

                            $arrValues[] = $objFile->uuid;
                        } else {
                            System::getContainer()
                                ->get('monolog.logger.contao')
                                ->log(LogLevel::ERROR, \sprintf('File "%s" do not exist', $strPath), ['contao' => new ContaoContext(__CLASS__ . '::' . __FUNCTION__)]);
                        }
                    }
                }

                return \count($arrValues) > 1 ? \serialize($arrValues) : \implode('', $arrValues);

            case 'DATE':

                if (Toolkit::isEmpty($strValue)) return 0;

                try {
                    $objDate = new Date($strValue, $arrOptions['datimFormat']);
                    return $objDate->tstamp;
                } catch (\Exception $objError) {
                    System::getContainer()
                        ->get('monolog.logger.contao')
                        ->log(LogLevel::ERROR, $objError->getMessage(), ['contao' => new ContaoContext(__CLASS__ . '::' . __FUNCTION__)]);
                }

                return 0;
        }

        return '';
    }
}