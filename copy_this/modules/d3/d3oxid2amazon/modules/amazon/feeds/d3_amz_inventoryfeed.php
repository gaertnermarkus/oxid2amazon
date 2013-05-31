<?php

/**
 *  az_amz_inventoryfeed => d3oxid2amazon/feeds/d3_amz_inventoryfeed
 */
class d3_amz_inventoryfeed extends d3_amz_inventoryfeed_parent
{

    public function getUpdateXml($id)
    {
        $product = $this->_getProduct($id);
        #dumpvar($product);
        
        $sSkuProp = $this->getSkuProperty();
        $sSkuValue = $this->cutEanNumber($product->$sSkuProp->value);

        //Vaterartikel übergeben keinen Lagerbestand
        if ($product->oxarticles__oxvarcount->value)
            return false;

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

    function _GetD3Delivery($product, $iStock)
    {
        if ($product->oxarticles__oxstockflag->value == 1 && $iStock < 1)
            return 10;
        else
            return 1;
    }

    /**
     * remove 1 from start and end of Sting
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
    
    function _GetD3Stock($iStock)  // $iStock berücksichtigt schon eine ggf. eingetragene Reserve
    {
        if ($iStock < 1)
            return 0;
        else
            return $iStock;
    }

}