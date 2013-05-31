<?php

/**
 * D3 MG 2012-04-04 
 */

class az_amz_categories extends oxbase
{

    protected $_sCoreTable = 'az_amz_categories2amzoncategories';
    protected $_sClassName = 'az_amz_categories';

    public function setCategoryMapping($soxId, $aThemeData)
    {
        //Eintraege loeschen
        $this->_DeleteAllItemsForCategory($soxId);
        //Eintraege schreiben
        return $this->_SaveCategoryMapping($soxId, $aThemeData);
    }

    protected function _SaveCategoryMapping($soxId, $aThemeData)
    {

        $oDB = oxDb::getDb(true);
        $sTable = getViewName($this->_sCoreTable);
        $sInsert = "";
        $iCounter = 0;
        foreach ($aThemeData as $aCat)
        {
            if ($aCat['CATID'])
            {
                $iCounter++;
                $sInsert = "INSERT INTO " . $sTable . " SET OXID=" . $oDB->quote(oxUtilsObject::getInstance()->generateUID()) . ", OXOBJECTID=" . $oDB->quote($soxId) . ", OXTYPE='oxcategory', OXSORT=" . $oDB->quote($iCounter) . ", D3AMAZONCATID=" . $oDB->quote($aCat['CATID']);
                #echo "<br>" . $sInsert;
                $oDB->execute($sInsert);
            }
            $sInsert = "";

            //Nur 5 Elemente
            #if($iCounter > 50)
            #    break;
        }
        return true;
    }

    /**
     * delete all entries 
     * 
     * @param string $soxId 
     */
    protected function _DeleteAllItemsForCategory($soxId)
    {
        $oDB = oxDb::getDb(true);
        $sTable = getViewName($this->_sCoreTable);
        $sDelete = "DELETE FROM " . $sTable . " WHERE OXOBJECTID=" . $oDB->quote($soxId) . " and OXTYPE='oxcategory'";

        $oDB->execute($sDelete);
    }

    /**
     * Gibt die gemappten Amazon-Kategorien zurueck
     * 
     Array
        (
        [0] => Array
            (
                [OXID] => 35dcc67c6b6829e8759cc221e33e7912
                [OXOBJECTID] => de01474c2c460bf6a07fd0d89fb6ad04
                [OXTYPE] => oxcategory
                [OXSORT] => 1
                [D3AMAZONCATID] => 11048451
            )
        )
     * 
     * @param string $soxId der Kategorien
     * @return array 
     */
    public function getAmazonCategories4Category($soxId)
    {
        $oDB = oxDb::getDb(true);
        $sTable = getViewName($this->_sCoreTable);
        $sSelect = "SELECT * FROM " . $sTable . " WHERE OXOBJECTID=" . $oDB->quote($soxId) . " and OXTYPE='oxcategory' ORDER BY OXSORT DESC";
        #echo "<hr>".__FUNCTION__.": ".$sSelect;
        $aCats = array();
        $rs = $oDB->execute($sSelect);

        if ($rs != false && $rs->RecordCount() > 0)
        {
            while (!$rs->EOF)
            {
                $aCats[] = $rs->fields;
                $rs->moveNext();
            }

            #dumpvar($aCats);
            return $aCats;
        }
        else
            return $aCats;
    }

    /**
     * Ermittelt die Amazon-Kategorien für diesen Artikel, von den entsprechenden Kategorien
     * 
     * gibt Array der Amazon-Kategorien zurueck
     * 
     * @param object $oArticle
     * @return array 
     */
    public function searchAmazonCategories4Article($oArticle)
    {
        $soxId = '';
        $soxId = $this->getDefaultCategory($oArticle);
        $aAmazonCats = $this->getAmazonCategories4Category($soxId);
        
        return $aAmazonCats;
    }

    public function getDefaultCategory($oArticle)
    {
        $sLang = oxLang::getInstance()->getBaseLanguage();
        $oDB = oxDb::getDb();

        $sCatView = getViewName('oxcategories');
        $sO2CView = getViewName('oxobject2category');

        //selecting category
        $sQ = "select $sCatView.oxid from $sO2CView as oxobject2category left join $sCatView on $sCatView.oxid = $sO2CView.oxcatnid ";
        $sQ .= "where $sO2CView.oxobjectid=" . $oDB->quote($oArticle->getId()) . " and $sCatView.oxactive" . (($sLang) ? "_$sLang" : "") . " = 1 order by $sO2CView.oxtime limit 0,1";

        #echo "<hr>".__FUNCTION__.": ".$sQ;
        
        $soxid = $oDB->getOne($sQ);
        return $this->getParentKategorie($soxid);
    }

    /**
     * ermittelt dem Kategoriebaum aufsteigend die nächste Kategorie
     *
     * @param string $soxid
     * @return string
     * 	 
     */
    public function getParentKategorie($soxId)
    {
        $aCat = '';
        $sParentCat = '';
        $sTable = getViewName($this->_sCoreTable);
        $sCatView = getViewName('oxcategories');
        $oDB = oxDb::getDb(true);

        $sQuery = "SELECT azcat.* FROM ". $sTable ." AS azcat left join " . $sCatView . " oxc on azcat.oxobjectid = oxc.oxid
        WHERE azcat.oxsort='1'AND azcat.d3amazoncatid !=''AND oxc.oxid=" . $oDB->quote($soxId);

        #echo "<hr>".$sQuery;
        $rs = $oDB->execute($sQuery);
        if ($rs != false && $rs->RecordCount() > 0)
        {
            while (!$rs->EOF)
            {
                $aCat = $rs->fields['OXOBJECTID'];
                $rs->moveNext();
            }
            #dumpvar($aCat);
            if ($aCat == '')
            {
                $QueryParantCatId = "SELECT oxparentid FROM " . $sCatView . " WHERE oxid=" . $oDB->quote($soxId);
                $sParentCat = $oDB->getOne($QueryParantCatId);
                #$aCat = $this->getParentKategorie($aCat['oxparentid']);

                if (!$sParentCat)
                    return '';
                $aCat = $this->getParentKategorie($sParentCat);
            }

            return $aCat;
        }
        else
        {
            $QueryParantCatId = "SELECT oxparentid FROM " . $sCatView . " WHERE oxid=" . $oDB->quote($soxId);
            $sParentCat = $oDB->getOne($QueryParantCatId);

            if (!$sParentCat)
                return '';
            $aCat = $this->getParentKategorie($sParentCat);
            return $aCat;
        }
        return '';
    }

    /**
     * Read Amazoncats from CSV-File
     * 
     * @return array 
     */
    public function getAmazonCategoriesFromCSV()
    {
        $aAmazonCatsCSV = array();
        $sThemeFile = "de_garden_browse_tree_guide.csv";
        $sPath = getShopBasePath() . "/modules/d3/d3oxid2amazon/modules/amazon/themes/" . $sThemeFile;
        #echo $sPath;
        if(!file_exists($sPath))
            return $aAmazonCatsCSV;
        
        $handler = fOpen($sPath, "r");


        while (($data = fgetcsv($handler, 1000, ";", '"')) !== false)
        {
            #dumpvar($data);
            $aAmazonCatCSV['BrowseNode'] = $data[0];
            $aAmazonCatCSV['Cat'] = $data[1];
            $aAmazonCatsCSV[] = $aAmazonCatCSV;
        }
        #dumpvar($aAmazonCatsCSV);
        
        return $aAmazonCatsCSV;
    }
}