<?php

namespace Alnv\CatalogManagerImporterBundle;

use Contao\Widget;

class KeyValueMappingWizard extends Widget
{

    protected $blnSubmitInput = true;

    protected $strTemplate = 'be_widget';

    protected array $arrHeadOptions = [];

    protected array $arrDataTypes = [];

    protected array $arrColumns = [];


    public function __set($strKey, $varValue)
    {

        switch ($strKey) {
            default:
                parent::__set($strKey, $varValue);
                break;
        }
    }

    public function validate()
    {
        parent::validate();
    }

    public function generate()
    {

        $this->initialize();

        if (!\is_array($this->getHeadOptions) || empty($this->getHeadOptions)) return '';

        if (!\is_array($this->varValue) || !$this->varValue[0]) {

            $this->varValue = [];
        }

        if (!\count($this->arrHeadOptions)) return '';

        return
            '<table  class="tl_mappingwizard" id="ctrl_' . $this->strId . '" data-tabindex="' . 0 . '">' .
            '<thead>' .
            '<tr>' .
            '<th class="mappingwizard_head">' . $GLOBALS['TL_LANG']['tl_catalog_imports']['mappingHeader']['head'] . '</th>' .
            '<th class="mappingwizard_column">' . $GLOBALS['TL_LANG']['tl_catalog_imports']['mappingHeader']['column'] . '</th>' .
            '<th class="mappingwizard_type">' . $GLOBALS['TL_LANG']['tl_catalog_imports']['mappingHeader']['type'] . '</th>' .
            '<th class="mappingwizard_continue">' . $GLOBALS['TL_LANG']['tl_catalog_imports']['mappingHeader']['continue'] . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody>' . $this->parseCSVMap($intTabindex) . '</tbody>' .
            '</table>';
    }

    protected function parseCSVMap(&$intTabindex): string
    {

        $strRows = '';
        $intTotalColumns = \count($this->arrHeadOptions);

        for ($intIndex = 0; $intTotalColumns > $intIndex; $intIndex++) {
            $arrValue = $this->varValue[$intIndex] ?? [];
            $strRows .=
                '<tr>' .
                '<td class="mappingwizard_head"><select name="' . $this->strId . '[' . $intIndex . '][head]" class="tl_select" tabindex="' . $intTabindex++ . '">' . $this->getHeadOptions($intIndex) . '</select></td>' .
                '<td class="mappingwizard_column"><select name="' . $this->strId . '[' . $intIndex . '][column]" class="tl_select tl_chosen" tabindex="' . $intTabindex++ . '">' . $this->getColumnOptions($arrValue) . '</select></td>' .
                '<td class="mappingwizard_type"><select name="' . $this->strId . '[' . $intIndex . '][type]" class="tl_select" tabindex="' . $intTabindex++ . '">' . $this->getDataTypeOptions($arrValue) . '</select></td>' .
                '<td class="mappingwizard_continue"><input type="checkbox" value="1" tabindex="' . $intTabindex++ . '" name="' . $this->strId . '[' . $intIndex . '][continue]" ' . (isset($arrValue['continue']) && $arrValue['continue'] == '1' ? 'checked' : '') . '></td>' .
                '</tr>';
        }

        return $strRows;
    }

    protected function getHeadOptions($intIndex): string
    {
        return \sprintf('<option value="%s" %s>%s</option>', $this->arrHeadOptions[$intIndex], 'selected', $this->arrHeadOptions[$intIndex]);
    }

    protected function getDataTypeOptions($arrValue): string
    {

        $strOptions = '';

        foreach ($this->arrDataTypes as $strType => $strName) {
            $strOptions .= \sprintf('<option value="%s" %s>%s</option>', $strType, (isset($arrValue['type']) && $arrValue['type'] == $strType ? 'selected' : ''), $strName);
        }

        return $strOptions;
    }

    protected function getColumnOptions($arrValue): string
    {

        $strOptions = '<option value="">-</option>';

        foreach ($this->arrColumns as $strFieldname => $strColumn) {
            $strOptions .= \sprintf('<option value="%s" %s>%s</option>', $strFieldname, (isset($arrValue['column']) && $arrValue['column'] == $strFieldname ? 'selected' : ''), $strColumn);
        }

        return $strOptions;
    }

    protected function initialize(): void
    {

        if (!empty($this->getHeadOptions) && is_array($this->getHeadOptions)) {
            $this->import($this->getHeadOptions[0]);
            $this->arrHeadOptions = $this->{$this->getHeadOptions[0]}->{$this->getHeadOptions[1]}($this);
        }

        if (!empty($this->getDataTypes) && is_array($this->getDataTypes)) {
            $this->import($this->getDataTypes[0]);
            $this->arrDataTypes = $this->{$this->getDataTypes[0]}->{$this->getDataTypes[1]}($this);
        }

        if (!empty($this->getColumns) && is_array($this->getColumns)) {
            $this->import($this->getColumns[0]);
            $this->arrColumns = $this->{$this->getColumns[0]}->{$this->getColumns[1]}($this);
        }
    }
}