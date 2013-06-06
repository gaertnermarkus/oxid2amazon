<?php

/**
 * D3 MG 2012-06-11
 * oxemail =>d3oxid2amazon/core/d3_oxemail_amazon
 */
class d3_oxemail_amazon extends d3_oxemail_amazon_parent
{

    protected $_sOrderUserTemplateAmazonPlain = "email/plain/email_order_cust_amazon_plain.tpl";

    protected $_sOrderUserTemplateAmazon = "email/html/email_order_cust_amazon_html.tpl";

    protected $_sOrderOwnerTemplateAmazonPlain = "email/plain/email_order_owner_amazon_plain.tpl";

    protected $_sOrderOwnerTemplateAmazon = "email/html/email_order_owner_amazon_html.tpl";

    /**
     * bitte den Link zur Bewertung rausnehmen.
     * Die Angabe zur Versandart auch.
     * Folgende Artikel wurden soeben über Amazon bei Gartenmoebel.de bestellt:
     *
     * @param object $oOrder
     * @param string $sSubject
     *
     * @return bool
     */
    public function d3AmazonSendOrderEmailToUser($oOrder, $sSubject = NULL)
    {
        #echo "<br>sendOrderEmailToUser";

        $myConfig = $this->getConfig();

        // add user defined stuff if there is any
        $oOrder = $this->_addUserInfoOrderEMail($oOrder);

        $oShop = $this->_getShop();
        $this->_setMailParams($oShop);

        $oUser = $oOrder->getOrderUser();
        $this->setUser($oUser);

        // create messages
        $oSmarty = $this->_getSmarty();
        $this->setViewData("order", $oOrder);

        #if ($myConfig->getConfigParam("bl_perfLoadReviews"))
        {
            $this->setViewData("blShowReviewLink", FALSE);
        }

        // Process view data array through oxoutput processor
        $this->_processViewArray();

        #$this->setBody($oSmarty->fetch($this->_sOrderUserTemplateAmazon));
        #$this->setAltBody($oSmarty->fetch($this->_sOrderUserTemplateAmazonPlain));

        $this->setBody($oSmarty->fetch($this->_sOrderUserTemplate));
        $this->setAltBody($oSmarty->fetch($this->_sOrderUserPlainTemplate));

        $oLang    = oxLang::getInstance();
        $sSubject = $oLang->translateString(
                "D3_EMAIL_ORDER_SUBJECT_CUST_AMAZON"
            ) . "(#" . $oOrder->oxorder__oxordernr->value . ")";

        // #586A
        if($sSubject === NULL) {
            if($oSmarty->template_exists($this->_sOrderUserSubjectTemplate)) {
                $sSubject = $oSmarty->fetch($this->_sOrderUserSubjectTemplate);
            }
            else {
                $sSubject = $oShop->oxshops__oxordersubject->getRawValue(
                    ) . " (#" . $oOrder->oxorder__oxordernr->value . ")";
            }
        }

        $this->setSubject($sSubject);

        $sFullName = $oUser->oxuser__oxfname->getRawValue() . " " . $oUser->oxuser__oxlname->getRawValue();

        $this->setRecipient($oUser->oxuser__oxusername->value, $sFullName);
        #$this->setRecipient("d3test1@shopmodule.com", $sFullName);
        $this->setReplyTo($oShop->oxshops__oxorderemail->value, $oShop->oxshops__oxname->getRawValue());

        $blSuccess = $this->send();

        return $blSuccess;
    }

    /**
     * Folgende Artikel wurden soeben über Amazon bei Gartenmoebel.de bestellt:
     *
     * @param object $oOrder
     * @param string $sSubject
     *
     * @return bool
     */
    public function d3AmazonSendOrderEmailToOwner($oOrder, $sSubject = NULL)
    {
        #echo "<br>sendOrderEmailToOwner";

        $myConfig = $this->getConfig();

        $oShop = $this->_getShop();

        // cleanup
        $this->_clearMailer();

        // add user defined stuff if there is any
        $oOrder = $this->_addUserInfoOrderEMail($oOrder);

        $oUser = $oOrder->getOrderUser();
        $this->setUser($oUser);

        // send confirmation to shop owner
        // send not pretending from order user, as different email domain rise spam filters
        $this->setFrom($oShop->oxshops__oxowneremail->value);

        $oLang      = oxLang::getInstance();
        $iOrderLang = $oLang->getObjectTplLanguage();

        // if running shop language is different from admin lang. set in config
        // we have to load shop in config language
        if($oShop->getLanguage() != $iOrderLang) {
            $oShop = $this->_getShop($iOrderLang);
        }

        $this->setSmtp($oShop);

        // create messages
        $oSmarty = $this->_getSmarty();
        $this->setViewData("order", $oOrder);

        // Process view data array through oxoutput processor
        $this->_processViewArray();

        $oLang    = oxLang::getInstance();
        $sSubject = $oLang->translateString(
                "D3_EMAIL_ORDER_SUBJECT_CUST_AMAZON"
            ) . "(#" . $oOrder->oxorder__oxordernr->value . ")";

        $this->setBody($oSmarty->fetch($myConfig->getTemplatePath($this->_sOrderOwnerTemplate, FALSE)));
        $this->setAltBody($oSmarty->fetch($myConfig->getTemplatePath($this->_sOrderOwnerPlainTemplate, FALSE)));

        //Sets subject to email
        // #586A
        if($sSubject === NULL) {
            if($oSmarty->template_exists($this->_sOrderOwnerSubjectTemplate)) {
                $sSubject = $oSmarty->fetch($this->_sOrderOwnerSubjectTemplate);
            }
            else {
                $sSubject = $oShop->oxshops__oxordersubject->getRawValue(
                    ) . " (#" . $oOrder->oxorder__oxordernr->value . ")";
            }
        }

        $this->setSubject($sSubject);
        $this->setRecipient($oShop->oxshops__oxowneremail->value, $oLang->translateString("order"));
        #$this->setRecipient("d3test1@shopmodule.com", $oLang->translateString("order"));

        if($oOrder->getOrderUser()->oxuser__oxusername->value != "admin") {
            $this->setReplyTo($oOrder->getOrderUser()->oxuser__oxusername->value);
        }

        $blSuccess = $this->send();

        // add user history
        /** @var oxremark $oRemark */
        $oRemark                       = oxNew("oxremark");
        $oRemark->oxremark__oxtext     = new oxField($this->getAltBody(), oxField::T_RAW);
        $oRemark->oxremark__oxparentid = new oxField($oOrder->getOrderUser()->getId(), oxField::T_RAW);
        $oRemark->oxremark__oxtype     = new oxField("o", oxField::T_RAW);
        $oRemark->save();

        if($myConfig->getConfigParam('iDebug') == 6) {
            oxUtils::getInstance()->showMessageAndExit("");
        }

        return $blSuccess;
    }

}