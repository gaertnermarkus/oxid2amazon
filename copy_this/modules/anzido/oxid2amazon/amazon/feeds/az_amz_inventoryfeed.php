<?php
class Az_Amz_InventoryFeed extends Az_Amz_Feed
{
    protected $_messageType = 'Inventory';

    /**
     * Feed name
     *
     * @var string
     */
    protected $_sFeedName = 'Pricing feed';

    /**
     * Feed Action
     *
     * @var string
     */
    protected $_sAction = Az_Amz_Feed::TYPE_INVENTORY;

    /**
     * File name base
     *
     * @var $_sFileNameBase
     */
    protected $_sFileNameBase = 'inventory_feed';

    public function getUpdateXml($id)
    {
        $product = $this->_getProduct($id);

        $sSkuProp  = $this->getSkuProperty();
        $sSkuValue = $this->prepareEanNumber($product->$sSkuProp->value);

        //Vaterartikel übergeben keinen Lagerbestand
        if($product->oxarticles__oxvarcount->value) {
            return FALSE;
        }

        $iStockReserve = $product->oxarticles__az_amz_stock_reserve->value;
        if($iStockReserve <= 0) {
            $iStockReserve = $product->getCategory()->oxcategories__az_amz_stock_reserve->value;
            if($iStockReserve <= 0) {
                $iStockReserve = (int)$this->_getAmzConfig()->iDefaultStockReserve;
            }
        }
        $iStock = $product->oxarticles__oxstock->value - $iStockReserve;
        if($iStock < 0) {
            $iStock = 0;
        }

        //Artikel auf bei amazon inaktiv setzten
        if($product->getFieldData('oxactive') == 0) {
            $iStock = 0;
        }

        $sXml = '<Message>' . $this->nl;
        $sXml .= '<MessageID>' . (++$this->_messageId) . '</MessageID>' . $this->nl;
        $sXml .= '<Inventory>' . $this->nl;
        $sXml .= '<SKU>' . $sSkuValue . '</SKU>' . $this->nl;
        $sXml .= $this->_getXmlIfExists('Quantity', $this->_GetStock($iStock)) . $this->nl;
        /* $sXml .= $this->_getXmlIfExists('FulfillmentLatency', $this->_GetD3Delivery($product, $iStock)); */
        $sXml .= '</Inventory>' . $this->nl;
        $sXml .= '</Message>' . $this->nl;

        return $sXml;
    }
}