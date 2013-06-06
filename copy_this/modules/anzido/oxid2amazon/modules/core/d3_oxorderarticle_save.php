<?php

/**
 * D3 MG 2012-05-02
 * oxorderarticle => d3oxid2amazon/core/d3_oxorderarticle_save

 */

class d3_oxorderarticle_save extends d3_oxorderarticle_save_parent
{
    public function save()
    {
        //wir prüfen, ob es noch keine oxId gibt (also Neubestellung) UND in der Order amzorderid gesetzt ist

        $sAmzId = oxDb::getDb()->GetOne(
            "SELECT amzorderid FROM oxorder WHERE oxid = '" . $this->oxorderarticles__oxorderid->value . "'"
        );

        if($sAmzId && !$this->oxorderarticles__oxid->value) {
            /** @var oxarticle $oArticle */
            $oArticle = oxNew("oxarticle");
            $oArticle->Load($this->oxorderarticles__oxartid->value);
            $iNewStock = $oArticle->oxarticles__oxstock->value - $this->oxorderarticles__oxamount->value;
            $oArticle->oxarticles__oxstock = new oxField($iNewStock);
            $oArticle->Save();
        }

        parent::save();
    }

    public function getThumbnailUrl($bSsl = NULL)
    {
        $oArticle = $this->getArticle();
        #$oArticle = oxNew("oxarticle");
        #$oArticle->Load($this->oxorderarticles__oxartid->value);

        $sImgName = FALSE;
        $sDirname = "product/1/";
        if(!$this->_IsFieldEmpty("oxarticles__oxthumb")) {
            $sImgName = basename($oArticle->oxarticles__oxthumb->value);
            $sDirname = "product/thumb/";
        }
        elseif(!$this->_IsFieldEmpty("oxarticles__oxpic1")) {
            $sImgName = basename($oArticle->oxarticles__oxpic1->value);
        }

        $sSize = $this->getConfig()->getConfigParam('sThumbnailsize');
        return oxPictureHandler::getInstance()->getProductPicUrl($sDirname, $sImgName, $sSize, 0, $bSsl);
    }

    /**
     * copy from oxarticle
     * Detects if field is empty.
     *
     * @param string $sFieldName Field name
     *
     * @return bool
     */
    protected function _IsFieldEmpty($sFieldName)
    {
        $mValue = $this->$sFieldName->value;

        if(is_null($mValue)) {
            return TRUE;
        }

        if($mValue === '') {
            return TRUE;
        }

        $aDoubleCopyFields = array('oxarticles__oxprice', 'oxarticles__oxvat');

        if(!$mValue && in_array($sFieldName, $aDoubleCopyFields)) {
            return TRUE;
        }

        if(!strcmp($mValue, '0000-00-00 00:00:00') || !strcmp($mValue, '0000-00-00')) {
            return TRUE;
        }

        $sFieldName = strtolower($sFieldName);

        if($sFieldName == 'oxarticles__oxicon' && (strpos($mValue, "nopic_ico.jpg") !== FALSE || strpos(
                    $mValue,
                    "nopic.jpg"
                ) !== FALSE)
        ) {
            return TRUE;
        }

        if(strpos($mValue, "nopic.jpg") !== FALSE && ($sFieldName == 'oxarticles__oxthumb' || substr(
                    $sFieldName,
                    0,
                    17
                ) == 'oxarticles__oxpic' || substr($sFieldName, 0, 18) == 'oxarticles__oxzoom')
        ) {
            return TRUE;
        }

        return FALSE;
    }

}