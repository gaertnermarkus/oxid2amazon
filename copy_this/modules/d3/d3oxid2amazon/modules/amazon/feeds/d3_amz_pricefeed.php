<?php

/**
 * 
 *  az_amz_pricefeed => d3oxid2amazon/feeds/d3_amz_pricefeed
 */

class d3_amz_pricefeed extends d3_amz_pricefeed_parent
{

    public function getUpdateXml($id)
    {
        #$oAmzConfig = $this->_getAmzConfig();
        $oDestination = $this->getDestination();
        $aCurrencies = oxConfig::getInstance()->getCurrencyArray();
        
        $product = $this->_getProduct($id);
        $sSkuProp = $this->getSkuProperty();
        $sSkuValue = $this->cutEanNumber($product->$sSkuProp->value);        

        //Vaterartikel übergeben keine Preise
        if ($product->oxarticles__oxvarcount->value)
            return false;

        $sXml = '<Message>' . $this->nl;
        $sXml .= '<MessageID>' . (++$this->_messageId) . '</MessageID>' . $this->nl;
        $sXml .= '<Price>' . $this->nl;
        $sXml .= '<SKU>' . $sSkuValue . '</SKU>' . $this->nl;
        $sXml .= '<StandardPrice currency="' . $aCurrencies[$oDestination->az_amz_destinations__az_currency->value]->name . '">' . $product->getPrice()->getBruttoPrice() . '</StandardPrice>' . $this->nl;
        $sXml .= '</Price>' . $this->nl;
        $sXml .= '</Message>' . $this->nl;

        return $sXml;
    }
    
    /**
     * remove 1 from start and end of Sting
     * 
     * 
     * @param string $sArtikelNr
     * @return string $sArtikelNrCut
     */    
    public function cutEanNumber($sArtikelNr)
    {
        $iPos1 = 0;
        $iPos2 = strlen($sArtikelNr) - 1;
        $sArtikelNrCut = '';

        if ((substr($sArtikelNr, $iPos1, 1) == '1' && substr($sArtikelNr, $iPos2, 1) == '1'))
            $sArtikelNrCut = substr($sArtikelNr, $iPos1 + 1, $iPos2 - 1);
        else
            $sArtikelNrCut = $sArtikelNr;

        return $sArtikelNrCut;
    }    

}