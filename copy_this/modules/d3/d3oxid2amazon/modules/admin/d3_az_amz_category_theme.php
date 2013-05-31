<?php

/**
 * D3 MG 2012-04-04
 * 
 * az_amz_category_theme =>d3oxid2amazon/admin/d3_az_amz_category_theme
 */


class d3_az_amz_category_theme extends d3_az_amz_category_theme_parent
{     
    /**
     * Speichert die Kategorien
     * 
     * @return bool 
     */
    public function saveAmazonCats()
    {
        $aThemeData = oxConfig::getParameter('amazonCats');
        $soxId = oxConfig::getParameter("oxid");

        if (!$soxId)
            return;        
        
        $oaz_amz_categories = oxnew('az_amz_categories');
        $oaz_amz_categories->setCategoryMapping($soxId, $aThemeData);
    }

    /**
     * Gibt die Kategorien zurueck
     * 
     * @param string $sOxid
     * @return array 
     */
    public function getAmazonCategories4category($sOxid)
    {

        $aAmazonCats = array();
        if (!$sOxid)
            return $aAmazonCats;

        $oaz_amz_categories = oxnew('az_amz_categories');
        $aAmazonCats = $oaz_amz_categories->getAmazonCategories4Category($sOxid);
        return $aAmazonCats;
    }
    

    public function getAmazonCategoriesFromCSV()
    {
        $aAmazonCats = array();
        $oaz_amz_categories = oxnew('az_amz_categories');
        $aAmazonCats = $oaz_amz_categories->getAmazonCategoriesFromCSV();        
        #dumpvar($aAmazonCats);
        return $aAmazonCats;
    }
    
}