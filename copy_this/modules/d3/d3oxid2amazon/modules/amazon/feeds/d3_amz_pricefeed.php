<?php

/**
 * 
 *  az_amz_pricefeed => d3oxid2amazon/feeds/d3_amz_pricefeed
 */

class d3_amz_pricefeed extends d3_amz_pricefeed_parent
{

    /**
     * @param $id
     *
     * @return bool|string
     */
    public function getUpdateXml($id)
    {
        #$oAmzConfig = $this->_getAmzConfig();
        $oDestination = $this->getDestination();
        $aCurrencies = oxConfig::getInstance()->getCurrencyArray();
        
        $product = $this->_getProduct($id);
        $sSkuProp = $this->getSkuProperty();
        $sSkuValue = $this->prepareEanNumber($product->$sSkuProp->value);

        //Vaterartikel übergeben keine Preise
        if ($product->oxarticles__oxvarcount->value)
            return FALSE;

        $sXml = '<Message>' . $this->nl;
        $sXml .= '<MessageID>' . (++$this->_messageId) . '</MessageID>' . $this->nl;
        $sXml .= '<Price>' . $this->nl;
        $sXml .= '<SKU>' . $sSkuValue . '</SKU>' . $this->nl;
        $sXml .= '<StandardPrice currency="' . $aCurrencies[$oDestination->az_amz_destinations__az_currency->value]->name . '">' . $product->getPrice()->getBruttoPrice() . '</StandardPrice>' . $this->nl;
        $sXml .= '</Price>' . $this->nl;
        $sXml .= '</Message>' . $this->nl;

        return $sXml;
    }

}