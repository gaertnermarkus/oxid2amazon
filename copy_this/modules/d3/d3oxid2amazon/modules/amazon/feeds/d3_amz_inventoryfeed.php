<?php

/**
 *  az_amz_inventoryfeed => d3oxid2amazon/feeds/d3_amz_inventoryfeed
 */
class d3_amz_inventoryfeed extends d3_amz_inventoryfeed_parent
{

    /**
     * @param $id
     *
     * @return bool|string
     */
    public function getUpdateXml($id)
    {
        $product = $this->_getProduct($id);
        #dumpvar($product);
        
        $sSkuProp = $this->getSkuProperty();
        $sSkuValue = $this->prepareEanNumber($product->$sSkuProp->value);

        //Vaterartikel übergeben keinen Lagerbestand
        if ($product->oxarticles__oxvarcount->value)
            return FALSE;

        $iStockReserve = $product->oxarticles__az_amz_stock_reserve->value;
        if ($iStockReserve <= 0)
        {
            $iStockReserve = $product->getCategory()->oxcategories__az_amz_stock_reserve->value;
            if ($iStockReserve <= 0)
            {
                $iStockReserve = (int) $this->_getAmzConfig()->iDefaultStockReserve;
            }
        }
        $iStock = $product->oxarticles__oxstock->value - $iStockReserve;
        if ($iStock < 0)
            $iStock = 0;
        
        #echo "<hr>";
        #echo $product->getFieldData('oxactive');
        #echo "<hr>";

        //Artikel auf bei amazon inaktiv setzten
        if ($product->getFieldData('oxactive') == 0)
            $iStock = 0;


        $sXml = '<Message>' . $this->nl;
        $sXml .= '<MessageID>' . (++$this->_messageId) . '</MessageID>' . $this->nl;
        $sXml .= '<Inventory>' . $this->nl;
        $sXml .= '<SKU>' . $sSkuValue . '</SKU>'. $this->nl;
        $sXml .= $this->_getXmlIfExists('Quantity', $this->_GetD3Stock($iStock)). $this->nl;
        /* $sXml .= $this->_getXmlIfExists('FulfillmentLatency', $this->_GetD3Delivery($product, $iStock)); */
        $sXml .= '</Inventory>' . $this->nl;
        $sXml .= '</Message>' . $this->nl;

        return $sXml;
    }
}