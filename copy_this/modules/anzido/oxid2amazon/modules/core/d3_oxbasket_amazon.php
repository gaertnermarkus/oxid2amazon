<?php

/**
 * oxbasket =>d3oxid2amazon/core/d3_oxbasket_amazon
 * D3 MG 2012-07-17
 * - Dreigaben entfernen / _addBundles(
 * - d3calculateBasket4Amazon umbenannt

 */
class d3_oxbasket_amazon extends d3_oxbasket_amazon_parent
{

    public function d3calculateBasket4Amazon()
    {
        if(!$this->isEnabled()) {
            return;
        }

        if(!$this->_blUpdateNeeded && !$blForceUpdate) {
            return;
        }

        $this->_aCosts = array();
        /** @var oxprice _oPrice */
        $this->_oPrice = oxNew('oxprice');
        $this->_oPrice->setBruttoPriceMode();

        //  1. saving basket to the database
        $this->_save();

        //  2. remove all bundles
        $this->_clearBundles();

        //  3. generate bundle items
        //  D3 Remove DiscountArticle
        #$this->_addBundles();

        // reserve active basket
        if($this->getConfig()->getConfigParam('blPsBasketReservationEnabled')) {
            $this->getSession()->getBasketReservations()->reserveBasket($this);
        }

        //  4. calculating item prices
        $this->_calcItemsPrice();

        //  5. calculating/applying discounts
        $this->_calcBasketDiscount();

        //  6. calculating basket total discount
        $this->_calcBasketTotalDiscount();

        //  7. check for vouchers
        $this->_calcVoucherDiscount();

        //  8. applies all discounts to pricelist
        $this->_applyDiscounts();

        //  9. calculating additional costs:
        //  9.1: delivery
        $this->setCost('oxdelivery', $this->_calcDeliveryCost());

        //  9.2: adding wrapping costs
        $this->setCost('oxwrapping', $this->_calcBasketWrapping());

        //  9.3: adding payment cost
        $this->setCost('oxpayment', $this->_calcPaymentCost());

        //  9.4: adding TS protection cost
        $this->setCost('oxtsprotection', $this->_calcTsProtectionCost());

        //  10. calculate total price
        $this->_calcTotalPrice();

        //  11. setting deprecated values
        $this->_setDeprecatedValues();

        //  12.setting to up-to-date status
        $this->afterUpdate();

        #$this->calculateBasket();
    }
}

