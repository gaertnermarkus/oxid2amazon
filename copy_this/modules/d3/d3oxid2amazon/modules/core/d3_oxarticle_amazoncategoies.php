<?php

/**
 * D3 MG 2012-04-18
 * 
 * oxarticle =>d3oxid2amazon/core/d3_oxarticle_amazoncategoies
 *  
 */
class d3_oxarticle_amazoncategoies extends d3_oxarticle_amazoncategoies_parent
{

    /**
     * Gibt die Kategorien 
     * @return array 
     */
    public function d3searchAmazonCategories4Article()
    {
        $aAmazonCats = array();
        $oaz_amz_categories = oxnew('az_amz_categories');
        $aAmazonCats = $oaz_amz_categories->searchAmazonCategories4Article($this);
        return $aAmazonCats;
    }

    /**
     * 
     * @return array
     */
    public function d3GetSearchTermsForAmazon()
    {
        $sSearch = $this->getFieldData('oxsearchkeys');
        return $this->_prepareSearchTermsForAmazon($sSearch);
    }

    /**
     *
     * @param string $sSearch 
     * @return array
     */
    protected function _prepareSearchTermsForAmazon($sSearch)
    {
        $aSearchTerms = array();
        for ($iCount = 1; $iCount <= 5; $iCount++)
        {
            $aResult = $this->_getPartOfSearchTermsText($sSearch);
            #dumpvar($aResult);
            $aSearchTerms[$iCount]['SearchTerm'] = $aResult['sPart'];
            $sSearch = $aResult['sSearch'];

            $aResult = array();
        }
        #dumpvar($aSearchTerms);
        return $aSearchTerms;
    }

    /**
     * Gibt Teiltext zurueeck
     * @param string $sSearch
     * @return array 
     */
    protected function _getPartOfSearchTermsText($sSearch)
    {
        //Leerzeichen entfernen
        $sSearch = trim($sSearch);

        //Teiltzeichenkette fuer Position
        $sSearch2 = $this->_d3Trim(substr($this->_d3Trim($sSearch), 0, 49));

        //Leerezeichnen-Postion suchen
        $iPos = strrpos($sSearch2, " ");

        //50 Zeichen zurueck geben
        $s50Zeichen = substr($sSearch, 0, $iPos);

        //Zeichenkette kuerzen
        $sSearch = $this->_d3Trim(substr($sSearch, $iPos));

        return array("sSearch" => $sSearch, "sPart" => $s50Zeichen);
    }

    /**
     * trim 
     * @param string $sString
     * @return string 
     */
    protected function _d3Trim($sString)
    {
        return trim(rtrim($sString));
    }

     /**
     * Gibt die Bildpfade fuer die ZoomBilder zurueck
     * wrapper fuer getZoomPictureUrl
     * gibt "" zurueck wenn nopic.jpg enthalten ist
     * 
     * @param integer $iIndex
     * @return string 
     */
    public function d3AmazonGetZoomPictureUrl($iIndex)
    {
        $sPic = $this->getZoomPictureUrl($iIndex);

        $iPos = strpos(strtolower($sPic), "nopic.jpg");
        if ($iPos !== false)
            $sPic = "";

        return $sPic;
    }

    public function d3AmazonShrink()
    {
        $sText = $this->oxarticles__ahtportaltext->rawValue;
        
        #$sText = $this->shrink($sText, 2000, false);
        $sText = $this->shrink($sText, 1970, false);
        #$sText = str_replace("\r\n", "<br>", $sText);
        #$sText = str_replace("\n", "<br>", $sText);
        return $sText;
    }

    /**
     * Quelle dynexportbase.php
     * @param type $sInput
     * @param type $iMaxSize
     * @param type $blRemoveNewline
     * @return string 
     */
    public function shrink($sInput, $iMaxSize, $blRemoveNewline = true)
    {
        if ($blRemoveNewline)
        {
            $sInput = str_replace("\r\n", " ", $sInput);
            $sInput = str_replace("\n", " ", $sInput);
        }

        $sInput = str_replace("\t", "    ", $sInput);

        // remove html entities, remove html tags
        #$sInput = $this->_unHTMLEntities(strip_tags($sInput));

        $oStr = getStr();
        if ($oStr->strlen($sInput) > $iMaxSize - 3)
        {
            $sInput = $oStr->substr($sInput, 0, $iMaxSize - 5) . "...";
        }

        return $sInput;
    }

    /**
     * Quelle dynexportbase.php
     * 
     * Replace HTML Entities
     * Replacement for html_entity_decode which is only available from PHP 4.3.0 onj
     *
     * @param string $sInput string to replace
     *
     * @return string
     */
    protected function _unHtmlEntities($sInput)
    {
        $aTransTbl = array_flip(get_html_translation_table(HTML_ENTITIES));
        return strtr($sInput, $aTransTbl);
    }
    
}