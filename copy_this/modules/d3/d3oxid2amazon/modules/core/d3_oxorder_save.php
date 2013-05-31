<?php

/**
 * D3 MG 2012-05-02
 * oxorder =>d3oxid2amazon/core/d3_oxorder_save
 *
 */
class d3_oxorder_save extends d3_oxorder_save_parent
{

    protected $_blExplodeError = false;

    public function save()
    {
        // wir müssen vor dem Speichern von Amazon-Bestellungen noch einige Dinge tun
        // Prüfe, ob wir gerade eine frische Amazon-Bestellung speichern wollen

        if (!$this->oxorder__oxorderdate->value && $this->oxorder__amzorderid->value) {
            $this->_D3InsertBill();
            $this->_D3ExplodeDelName();
            $this->_D3ExplodeDelStreet();
            $this->_D3ExplodeBillName();
            $this->_D3ExplodeBillStreet();

            $this->oxorder__oxpaid = new oxField(date("Y-m-d H:i:s"));
        }

        return parent::save();
    }

    protected function _D3InsertBill()
    {
        if ($this->oxorder__oxbillcity->value)
            return;

        //wenn RE- und DEL-Adresse identisch ist, muß ein Großteil der RE-Daten aus DEL gefüllt werden
        $this->oxorder__oxbillstreet = new oxField($this->oxorder__oxdelstreet->value, oxField::T_RAW);
        $this->oxorder__oxbillcity = new oxField($this->oxorder__oxdelcity->value, oxField::T_RAW);
        $this->oxorder__oxbillzip = new oxField($this->oxorder__oxdelzip->value, oxField::T_RAW);
        $this->oxorder__oxbillfon = new oxField($this->oxorder__oxdelfon->value, oxField::T_RAW);
        $this->oxorder__oxbillfax = new oxField($this->oxorder__oxdelfax->value, oxField::T_RAW);
        $this->oxorder__oxbillcompany = new oxField($this->oxorder__oxdelcompany->value, oxField::T_RAW);
        $this->oxorder__oxbillcountryid = new oxField($this->oxorder__oxdelcountryid->value, oxField::T_RAW);
    }

    protected function _D3ExplodeDelName()
    {

        $sString = trim($this->oxorder__oxdellname->value);
        $iLength = strlen($sString);

        $iPos = strrpos($sString, " ");

        if ($iPos) {
            $this->oxorder__oxdelfname = new oxField(substr($sString, 0, $iPos), oxField::T_RAW);
            $this->oxorder__oxdellname = new oxField(substr($sString, ($iLength - $iPos - 1) * -1), oxField::T_RAW);
        } else
            $this->_blExplodeError = true;
    }

    protected function _D3ExplodeDelStreet()
    {

        $sString = trim($this->oxorder__oxdelstreet->value);
        $iLength = strlen($sString);

        $iPos = strrpos($sString, " ");
        $iPos2 = strrpos($sString, ".");

        if ($iPos) {
            $this->oxorder__oxdelstreet = new oxField(substr($sString, 0, $iPos), oxField::T_RAW);
            $this->oxorder__oxdelstreetnr = new oxField(substr($sString, ($iLength - $iPos - 1) * -1), oxField::T_RAW);
        } elseif ($iPos2) {
            $this->oxorder__oxdelstreet = new oxField(substr($sString, 0, $iPos2), oxField::T_RAW);
            $this->oxorder__oxdelstreetnr = new oxField(substr($sString, ($iLength - $iPos2 - 1) * -1), oxField::T_RAW);
        } else
            $this->_blExplodeError = true;
    }

    protected function _D3ExplodeBillName()
    {

        $sString = trim($this->oxorder__oxbilllname->value);
        $iLength = strlen($sString);

        $iPos = strrpos($sString, " ");

        if ($iPos) {
            $this->oxorder__oxbillfname = new oxField(substr($sString, 0, $iPos), oxField::T_RAW);
            $this->oxorder__oxbilllname = new oxField(substr($sString, ($iLength - $iPos - 1) * -1), oxField::T_RAW);
        } else
            $this->_blExplodeError = true;
    }

    protected function _D3ExplodeBillStreet()
    {

        $sString = trim($this->oxorder__oxbillstreet->value);
        $iLength = strlen($sString);

        $iPos = strrpos($sString, " ");
        $iPos2 = strrpos($sString, ".");

        if ($iPos) {
            $this->oxorder__oxbillstreet = new oxField(substr($sString, 0, $iPos), oxField::T_RAW);
            $this->oxorder__oxbillstreetnr = new oxField(substr($sString, ($iLength - $iPos - 1) * -1), oxField::T_RAW);
        } elseif ($iPos2) {
            $this->oxorder__oxbillstreet = new oxField(substr($sString, 0, $iPos2), oxField::T_RAW);
            $this->oxorder__oxbillstreetnr = new oxField(substr($sString, ($iLength - $iPos2 - 1) * -1), oxField::T_RAW);
        } else
            $this->_blExplodeError = true;
    }

    /**
     * Wrapper fuer d3getOrderBasket
     * @param bool $blStockCheck
     * @return object
     */
    public function d3getOrderBasket($blStockCheck = true)
    {
        return $this->_getOrderBasket($blStockCheck);
    }

    /**
     * Wrapper fuer _setPayment
     * @param string $sPaymentid
     * @return object
     */
    public function d3setPayment4Amazon($sPaymentid)
    {
        return $this->_setPayment($sPaymentid);
    }

    /**
     * Wrapper fuer _sendOrderByEmail
     * @param object $oUser
     * @param tyobjectpe $oBasket
     * @param tyobjectpe $oPayment
     * @return object
     */
    public function d3sendOrderByEmail($oUser = null, $oBasket = null, $oPayment = null)
    {
        oxSession::getInstance()->setBasket($oBasket);
        #return $this->_sendOrderByEmail($oUser, $oBasket, $oPayment);
        return $this->_d3sendOrderByEmail($oUser, $oBasket, $oPayment);
    }

    /**
     * Wrapper fuer _addOrderArticlesToBasket
     * @param object $oBasket
     * @param object $aOrderArticles
     */
    public function d3addOrderArticlesToBasket($oBasket, $aOrderArticles)
    {
        $this->_addOrderArticlesToBasket($oBasket, $aOrderArticles);
    }

    protected function _d3sendOrderByEmail($oUser = null, $oBasket = null, $oPayment = null)
    {
        $sSubject = "Ihre Bestellung über Amazon beim Onlineshop www.gartenmoebel.de (#11529)";
        $iRet = self::ORDER_STATE_MAILINGERROR;

        // add user, basket and payment to order
        $this->_oUser = $oUser;
        $this->_oBasket = $oBasket;
        $this->_oPayment = $oPayment;

        $oxEmail = oxNew('oxemail');

        // send order email to user
        #* 2012-08-20 MG wegen Rabatt ausschalten
        if ($oxEmail->d3AmazonSendOrderEmailToUser($this))
        {
            $iRet = self::ORDER_STATE_OK;
        }


        // send order email to shop owner
        $oxEmail->d3AmazonSendOrderEmailToOwner($this);

        return $iRet;
    }

}