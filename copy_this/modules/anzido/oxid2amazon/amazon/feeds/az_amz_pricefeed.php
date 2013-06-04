<?php
class Az_Amz_PriceFeed extends Az_Amz_Feed
{
    protected $_messageType = 'Price';

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
    protected $_sAction = Az_Amz_Feed::TYPE_PRICE;

    /**
     * File name base
     *
     * @var $_sFileNameBase
     */
    protected $_sFileNameBase = 'price_feed';

    public function getUpdateXml($id)
    {
        $oDestination = $this->getDestination();
        $aCurrencies  = oxConfig::getInstance()->getCurrencyArray();

        $product   = $this->_getProduct($id);
        $sSkuProp  = $this->getSkuProperty();
        $sSkuValue = $this->prepareEanNumber($product->$sSkuProp->value);

        //Vaterartikel übergeben keine Preise
        if($product->oxarticles__oxvarcount->value) {
            return FALSE;
        }

        $sXml = '<Message>' . $this->nl;
        $sXml .= '<MessageID>' . (++$this->_messageId) . '</MessageID>' . $this->nl;
        $sXml .= '<Price>' . $this->nl;
        $sXml .= '<SKU>' . $sSkuValue . '</SKU>' . $this->nl;
        $sXml .= '<StandardPrice currency="' . $aCurrencies[$oDestination->az_amz_destinations__az_currency->value]->name . '">' . $product->getPrice(
            )->getBruttoPrice() . '</StandardPrice>' . $this->nl;
        $sXml .= '</Price>' . $this->nl;
        $sXml .= '</Message>' . $this->nl;

        return $sXml;

    }
}