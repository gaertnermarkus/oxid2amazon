<?php

class az_amz_settings_main extends oxAdminDetails
{
	/**
     * Current class template name.
     * @var string
     */
    protected $_sThisTemplate = 'az/oxid2amazon/az_amz_settings_main.tpl';
    
    public function render()
    {
    	parent::render();
    	
    	$oArticle = oxNew('oxarticle');
    	$aFields = explode(',', $oArticle->getSelectFields());
    	//array_shift($aFields); // shift 'oxid' field
    	$aEanFields = array();
    	foreach($aFields as $val)
        {             
                $aField = explode(".", $val);                
                $sField = $aField[1];
    		$aEanFields[$sField] = $sField;
    	}
    	
    	
    	$aPictureFields = array();
    	foreach($aEanFields as $field) {
    		if(strpos($field, 'oxpic') === 0) {
                    if($field != 'oxpicsgenerated')
    			$aPictureFields[$field] = $field;
    		}
    	}
    	
    	$oAzConfig = oxNew('az_amz_config', oxConfig::getInstance()->getShopId());
    	
    	// set some defaults
    	$this->_aViewData['aArticleFields'] = $aEanFields;
    	$this->_aViewData['aPictureFields'] = $aPictureFields;
    	$this->_aViewData['aThemes']		= $oAzConfig->getAmazonThemes();    	
    	$this->_aViewData['oAzConfig'] 		= $oAzConfig;
    	$this->_aViewData['aPayments']		= $this->_getPayments();
    	$this->_aViewData['aShippings']		= $this->_getShippings();
    	
    	return $this->_sThisTemplate;
    }
    
    public function save()
    {
    	$myConfig = $this->getConfig();
    	$editVal = $myConfig->getParameter('editval');    	
    	$oAzConfig = oxNew('az_amz_config', $myConfig->getShopId());
    	$oAzConfig->assignArray($editVal);
    	$oAzConfig->saveToDatabase();
    }
    
    protected function _getPayments()
    {
    	$sSelect = "select oxid, oxdesc from oxpayments";
    	$aAllPayments = oxDb::getDb(true)->getAll($sSelect);
    	//dumpVar($aAllPayments);
        $aPayments = array();
    	
    	foreach ($aAllPayments as $aValues) {
    		$oPayment = new stdClass();
    		$oPayment->oxid		= $aValues['oxid'];
    		$oPayment->title	= $aValues['oxdesc'];
    		$aPayments[]		= $oPayment;
    	}
    	
    	return $aPayments;
    }
    
    protected function _getShippings()
    {
    	$sSelect = "select oxid, oxtitle from oxdeliveryset";
    	$aAllShipsets = oxDb::getDb(true)->getAll($sSelect);
        $aShipsets = array();
    	
    	foreach ($aAllShipsets as $aValues) {
    		$oShipset = new stdClass();
    		$oShipset->oxid		= $aValues['oxid'];
    		$oShipset->title	= $aValues['oxtitle'];
    		$aShipsets[]		= $oShipset;
    	}
    	
    	return $aShipsets;
    }
}