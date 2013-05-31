<?php

/**
 * D3 MG 2012-07-17 
 * - d3calculateBasket4Amazon und einkommentiert
 * 
 * az_amz_orders =>d3oxid2amazon/core/d3_amz_orders
 */

#ini_set('display_errors', -1);
#ini_set('error_reporting', -1);

class d3_amz_orders extends d3_amz_orders_parent
{

    public function importOrders()
    {
        $aOrders = $this->_getOrders();
        foreach ($aOrders as $aOrder)
        {
            if (!empty($aOrder))
            {
                $sUserId = $this->_d3getOrderUser($aOrder);
                $oUser = oxnew('oxuser');
                $oUser->load($sUserId);
                #dumpvar($oUser);

                $oAdress = $this->_d3getDelAdresse($aOrder);
                #dumpvar($oAdress);
                /* @var $oOrder oxorder */
                $oOrder = oxNew("oxorder");
                $oOrder->oxorder__amzorderid = oxnew("oxField", $aOrder['AMZORDERID']);
                $oOrder->oxorder__oxshopid = oxnew("oxField", $aOrder['OXSHOPID']);
                $oOrder->oxorder__oxuserid = oxnew("oxField", $oUser->getId());
                $oOrder->oxorder__oxbillemail = oxnew("oxField", $aOrder['BUYEREMAIL']);
                $oOrder->oxorder__oxbilllname = oxnew("oxField", $oUser->getFieldData('OXLNAME'));
                #$oOrder->oxorder__oxbilllname = oxnew("oxField", $oUser->oxuser__oxbilllname->rawValue);
                $oOrder->oxorder__oxbillfname = oxnew("oxField", $oUser->getFieldData('OXFNAME'));
                #$oOrder->oxorder__oxbillfname = oxnew("oxField", $oUser->oxuser__oxbillfname->rawValue);
                $oOrder->oxorder__oxbillstreet = oxnew("oxField", $aOrder['BUYERSTREET']);
                $oOrder->oxorder__oxbillcity = oxnew("oxField", $aOrder['BUYERCITY']);
                $oOrder->oxorder__oxbillzip = oxnew("oxField", $aOrder['BUYERZIP']);
                $oOrder->oxorder__oxbillcountryid = oxnew("oxField", $this->_getUserCountry($aOrder['BUYERCOUNTRYCODE']));
                $oOrder->oxorder__oxbillfon = oxnew("oxField", $aOrder['BUYERPHONE']);
                #$oOrder->oxorder__oxdelfname = oxnew("oxField", $oUser->getFieldData('OXDELFNAME'));
                $oOrder->oxorder__oxdelfname = oxnew("oxField", $oAdress->oxaddress__oxfname->rawValue);
                #$oOrder->oxorder__oxdellname = oxnew("oxField", $oUser->getFieldData('OXDELLNAME'));
                $oOrder->oxorder__oxdellname = oxnew("oxField", $oAdress->oxaddress__oxfname->rawValue);
                $oOrder->oxorder__oxdelcompany = oxnew("oxField", $aOrder['DELCOMPANY']);
                $oOrder->oxorder__oxdelstreet = oxnew("oxField", $aOrder['DELSTREET']);
                $oOrder->oxorder__oxdelcity = oxnew("oxField", $aOrder['DELCITY']);
                $oOrder->oxorder__oxdelzip = oxnew("oxField", $aOrder['DELZIP']);
                $oOrder->oxorder__oxdelcountryid = oxnew("oxField", $this->_getUserCountry($aOrder['DELCOUNTRYCODE']));
                $oOrder->oxorder__oxdelfon = oxnew("oxField", $aOrder['DELPHONE']);
                $oOrder->oxorder__oxfolder = oxnew("oxField", "ORDERFOLDER_NEW");
                $oOrder->oxorder__oxdeltype = oxnew("oxField", $this->_getAmazonShipSet($aOrder['DELSERVICELEVEL']));
                $oOrder->oxorder__oxpaymenttype = oxnew("oxField", $this->_getAmazonPayment());
                $oOrder->oxorder__oxcurrency = oxnew("oxField", "EUR");
                $oOrder->oxorder__oxcurrate = oxnew("oxField", 1);
                $oOrder->oxorder__oxtransstatus = oxnew("oxField", "OK");

                //Nr ebenfalls in OXTRANSID abspeichern
                $oOrder->oxorder__oxtransid = oxnew("oxField", $aOrder['AMZORDERID']);
                $oOrder->save();
                echo "<hr>";
                #dumpvar($oOrder);
                $sOrderId = $oOrder->getId();
                $dArticleSum = $this->_d3saveOrderArticles($aOrder['AMZORDERID'], $sOrderId);

                // if there are any errors on inserting order articles $this->_blOrderDelete will be set to true
                // this means: delete order and return immediately, do not set order sum - cause order is saved again there
                // DOC: such deleted orders stay in tmp tables with azprocessed flag = 0. they could be imported later.
                // TODO: clean up tmp tables

                if ($this->_blOrderDelete)
                {
                    $this->_deleteOrder($sOrderId);
                    return;
                }
                $this->_setOrderSum($oOrder, $dArticleSum);

                $this->_setOrderDone($aOrder['AMZORDERID']);

                $this->_D3setOrderDate($oOrder, $aOrder['AMZORDERDATE']);

                //E-Mail - Kunde + Admin
                #echo "<br>".__METHOD__." :: ".__LINE__;
                $this->_d3SendOrderByEmail($oOrder->getId());
            }
        }
    }

    /**
     * Set OrderDate manuel, 
     * 
     * @param object $oOrder
     * @param object $sDate 
     */
    protected function _D3setOrderDate($oOrder, $sDate)
    {

        #$oOrder->oxorder__oxorderdate->value = $sDate;

        $oUtilsDate = oxUtilsDate::getInstance();
        $sDate = date('Y-m-d H:i:s', $oUtilsDate->getTime());
        $sId = $oOrder->getId();
        $oDb = oxDb::getDb();
        $sUpdate = "UPDATE oxorder SET oxorderdate=" . $oDb->quote($sDate) . " WHERE oxid=" . $oDb->quote($sId);
        #echo "<br>" . $sUpdate;

        $oDb->execute($sUpdate);
    }

    protected function _d3getOrderUser($aAmzOrder)
    {
        $sId =  '';
        $sUserId = oxDb::getDb()->getOne("select oxid from oxuser where oxusername = '" . $aAmzOrder['BUYEREMAIL'] . "'");
        if (empty($sUserId))
        {

            $aName = $this->_separateAmazonName($aAmzOrder['BUYERNAME']);
            $aStreet = $this->_separateAmazonStreet($aAmzOrder['BUYERSTREET']);

            /* @var $oUser oxuser */
            $oUser = oxNew("oxuser");
            $oUser->oxuser__oxactive->value = '1';
            $oUser->oxuser__oxusername->value = $aAmzOrder['BUYEREMAIL'];
            $oUser->oxuser__oxlname->value = $aName['lname'];
            $oUser->oxuser__oxfname->value = $aName['fname'];
            $oUser->oxuser__oxfon->value = $aAmzOrder['BUYERPHONE'];
            $oUser->oxuser__oxstreet->value = $aStreet['street'];
            $oUser->oxuser__oxstreetnr->value = $aStreet['streetnr'];
            $oUser->oxuser__oxcity->value = $aAmzOrder['BUYERCITY'];
            $oUser->oxuser__oxzip->value = $aAmzOrder['BUYERZIP'];
            $oUser->oxuser__oxcountryid->value = $this->_getUserCountry($aAmzOrder['BUYERCOUNTRYCODE']);
            $oUser->save();
            $sId = $oUser->getId();
        }
        else
        {
            #$oUser = oxNew("oxuser");
            #$oUser->load($sUserId);
            $sId = $sUserId;
        }

        return $sId;
    }

    protected function _d3getDelAdresse($aAmzOrder)
    {
        $sUserId = oxDb::getDb()->getOne("select oxid from oxuser where oxusername = '" . $aAmzOrder['BUYEREMAIL'] . "'");

        /* @var $oAddress oxaddress */
        $oAddress = oxNew("oxaddress");
        $sAdressOxid = $this->_GetAdressOxid($sUserId);

        if (!$oAddress->load($sAdressOxid))
        {
            $aName = $this->_separateAmazonName($aAmzOrder['DELNAME']);
            $aStreet = $this->_separateAmazonStreet($aAmzOrder['DELSTREET']);

            $oAddress->oxaddress__oxfname = oxnew('oxfield', $aName['fname']);
            $oAddress->oxaddress__oxlname = oxnew('oxfield', $aName['lname']);
            $oAddress->oxaddress__oxsal = oxnew('oxfield', '');
            $oAddress->oxaddress__oxfon = oxnew('oxfield', $aAmzOrder['DELPHONE']);
            $oAddress->oxaddress__oxcompany = oxnew('oxfield', $aAmzOrder['DELCOMPANY']);
            $oAddress->oxaddress__oxstreet = oxnew('oxfield', $aStreet['street']);
            $oAddress->oxaddress__oxstreetnr = oxnew('oxfield', $aStreet['streetnr']);
            $oAddress->oxaddress__oxcity = oxnew('oxfield', $aAmzOrder['DELCITY']);
            $oAddress->oxaddress__oxzip = oxnew('oxfield', $aAmzOrder['DELZIP']);
            $oAddress->oxaddress__oxaddinfo = oxnew('oxfield', '');
            $oAddress->oxaddress__oxstateid = oxnew('oxfield', '');
            $oAddress->oxaddress__oxcountryid = oxnew('oxfield', $this->_getUserCountry($aAmzOrder['DELCOUNTRYCODE']));
            $oAddress->save();
        }
        else
        {
            $oAddress->load($sAdressOxid);
        }
        return $oAddress;
    }

    protected function _d3saveOrderArticles($sAmzOrderId, $sOrderId)
    {

        $dArticleSum = 0;
        $this->_dShippingcost = 0;
        $aVatPercent = array();

        $aOrderItems = $this->_getOrderArticles($sAmzOrderId);
        //dumpVar($aOrderItems);
        foreach ($aOrderItems as $aOrderItem)
        {
            /* @var $oOrderArticle oxorderarticle */
            $oOrderArticle = oxNew("oxorderarticle");
            $oArticle = $this->_getOrderArticle($aOrderItem['AMZSKU']);

            if (!$oArticle)
            {
                //die('article with SKU ' . $aOrderItem['AMZSKU'] . ' not found');
                $this->_oAZConfig->logError("order import error: article with SKU " . $aOrderItem['AMZSKU'] . " not found in article table\n");
                $this->_blOrderDelete = true;
                continue;
            }

            if ($oArticle->oxarticles__oxvat->value > 0)
                $dVatPercent = $oArticle->oxarticles__oxvat->value;
            else
                $dVatPercent = $this->getConfig()->getShopConfVar('dDefaultVAT');

            $aNetValues = $this->_getNetPrice($aOrderItem['AMZARTPRICE'], $dVatPercent);
            if (!in_array($dVatPercent, $aVatPercent))
                $aVatPercent[] = $dVatPercent;

            $oOrderArticle->oxorderarticles__oxartid->value = $oArticle->getId();
            $oOrderArticle->oxorderarticles__oxartnum->value = $oArticle->oxarticles__oxartnum->value;
            $oOrderArticle->oxorderarticles__oxtitle->value = $oArticle->oxarticles__oxtitle->value;
            $oOrderArticle->oxorderarticles__oxshortdesc->value = $oArticle->oxarticles__oxshortdesc->value;
            $oOrderArticle->oxorderarticles__oxselvariant->value = $oArticle->oxarticles__oxvarselect->value;

            $oOrderArticle->oxorderarticles__oxorderid->value = $sOrderId;
            $oOrderArticle->oxorderarticles__oxamount->value = $aOrderItem['AMZQUANTITY'];
            $oOrderArticle->oxorderarticles__oxnetprice->value = $aNetValues['dNetPrice'];
            $oOrderArticle->oxorderarticles__oxbrutprice->value = $aOrderItem['AMZARTPRICE'];
            $dArticleSum += $aOrderItem['AMZARTPRICE'];
            $oOrderArticle->oxorderarticles__oxvatprice->value = $aNetValues['dVatPrice'];
            $this->_dShippingcost += $aOrderItem['AMZSHIPPRICE'];


            /** Add D3 MG/TD START 2011_05_30  * */
            /* Artikel in oxorderarticles werden sonst "sporadisch ohne Vat und oxprice gespeichert" */
            $oOrderArticle->oxorderarticles__oxvat->value = $dVatPercent;
            $oOrderArticle->oxorderarticles__oxprice->value = $aOrderItem['AMZARTPRICE'] / $aOrderItem['AMZQUANTITY'];
            $oOrderArticle->oxorderarticles__oxbprice->value = $aOrderItem['AMZARTPRICE'] / $aOrderItem['AMZQUANTITY'];
            $oOrderArticle->oxorderarticles__oxnprice->value = $aNetValues['dNetPrice'] / $aOrderItem['AMZQUANTITY'];
            /** Add D3 MG/TD  * */
            $oOrderArticle->save();


            /* ADD D3 MG 2012-05-03 - Daten manuell an Artikel speichern */
            $this->_addParams2OrderArticle($oOrderArticle->getId(), $oArticle);

            // TODO for EE 2.7: write alternative function for updateArticleStock
            $oOrderArticle->updateArticleStock($oOrderArticle->oxorderarticles__oxamount->value * (-1), $this->getConfig()->getConfigParam('blAllowNegativeStock'));
        }


        /* ADD D3 MG 2011_11_28 bricht sonst ab */
        if ($this->_blOrderDelete == true)
            return 0;

        $this->_dMaxVatPercent = max($aVatPercent);

        return $dArticleSum;
    }

    /**
     * Add some paramter to orderarticle
     * oxinsert, oxthumb, oxpic1-3
     * oxsubclass
     * oxodershopid
     * 
     * 
     * @param string $sOxid
     * @param object $oArticle 
     */
    protected function _addParams2OrderArticle($sOxid, $oArticle)
    {
        $oDb = oxDb::getDb();
        $sUpdate = "UPDATE oxorderarticles SET 
            oxsubclass = 'oxarticle',
            oxordershopid = 'oxbaseshop',
            oxinsert = " . $oDb->quote(date('Y-m-d')) . ",
            oxthumb = " . $oDb->quote($oArticle->oxarticles__oxthumb->value) . ",   
            oxpic1 = " . $oDb->quote($oArticle->oxarticles__oxpic1->value) . ",   
            oxpic2 = " . $oDb->quote($oArticle->oxarticles__oxpic2->value) . ",   
            oxpic3 = " . $oDb->quote($oArticle->oxarticles__oxpic3->value) . "                
            
            WHERE oxid=" . $oDb->quote($sOxid);
        #echo "<br>" . $sUpdate;

        $oDb->execute($sUpdate);

        /*
          $oOrderArticle = oxNew("oxorderarticle");
          $oOrderArticle->load($sOxid);
          $oOrderArticle->oxorderarticles__oxsubclass = "oxarticle";
          $oOrderArticle->oxorderarticles__oxordershopid = "oxbaseshop";
          $oOrderArticle->oxorderarticles__oxinsert = date('Y-m-d');

          $oOrderArticle->oxorderarticles__oxthumb = $oArticle->oxarticles__oxthumb->value;
          $oOrderArticle->oxorderarticles__oxpic1 = $oArticle->oxarticles__oxpic1->value;
          $oOrderArticle->oxorderarticles__oxpic2 = $oArticle->oxarticles__oxpic2->value;
          $oOrderArticle->oxorderarticles__oxpic3 = $oArticle->oxarticles__oxpic3->value;
          $oOrderArticle->save();

         */
    }

    /**
     * separete Name from Amazon
     *
     * @param string $sName
     * @return array
     */
    protected function _separateAmazonName($sName)
    {
        $aName = array();

        $sString = trim($sName);
        $iLength = strlen($sString);

        $iPos = strrpos($sString, " ");

        if ($iPos)
        {
            $aName['fname'] = substr($sString, 0, $iPos);
            $aName['lname'] = substr($sString, ($iLength - $iPos - 1) * -1);
        }

        return $aName;
    }

    /**
     * separate Adress from Amazon
     * @param string $sStreet
     * @return array
     */
    protected function _separateAmazonStreet($sStreet)
    {
        $sString = trim($sStreet);
        $iLength = strlen($sString);

        $iPos = strrpos($sString, " ");
        $iPos2 = strrpos($sString, ".");

        $aStreet = array();

        if ($iPos)
        {
            $aStreet['street'] = substr($sString, 0, $iPos);
            $aStreet['streetnr'] = substr($sString, ($iLength - $iPos - 1) * -1);
        }
        elseif ($iPos2)
        {
            $aStreet['street'] = substr($sString, 0, $iPos2);
            $aStreet['streetnr'] = substr($sString, ($iLength - $iPos2 - 1) * -1);
        }
        else
        {
            $aStreet['street'] = $sString;
            $aStreet['streetnr'] = "";
        }

        return $aStreet;
    }

    /**
     * Send E-Mail to Owner and Customer
     * Set LangId
     * 
     * @param string $sOxid
     * @return bool 
     */
    protected function _d3SendOrderByEmail($sOxid)
    {
        $blOrderSend = false;
        /* @var $oOrder oxorder */
        $oOrder = oxnew('oxorder');
        $blOrderLoad = $oOrder->load($sOxid);

        if (!$blOrderLoad)
            return $blOrderSend;

        $oBasket = $oOrder->d3getOrderBasket();
        $oOrder->d3addOrderArticlesToBasket($oBasket, $oOrder->getOrderArticles(true));
        $oBasket->d3calculateBasket4Amazon();
        #$oBasket->calculateBasket();

        $oUser = $oOrder->getOrderUser();
        $oUserPayment = $oOrder->d3setPayment4Amazon($oBasket->getPaymentId());

        //LangId für Email
        $oLanguages = oxLang::getInstance();
        $iBaseLang = $oLanguages->getBaseLanguage();
        $iLang = $oOrder->getFieldData('oxlang');
        $oLanguages->setBaseLanguage($iLang);

        $blOrderSend = $oOrder->d3sendOrderByEmail($oUser, $oBasket, $oUserPayment);
        //Restore Language
        $oLanguages->setBaseLanguage($iBaseLang);

        return $blOrderSend;
    }

    public function readFiles()
    {
        $this->_readSourceDir();
        #var_dump($this->_aReportFileNames);

        #die();
        foreach ($this->_aReportFileNames as $sFileName)
        {
            $this->setCurrentFileName($sFileName);
            $this->_parseFileContent();
            $this->_moveOrderReport($sFileName);
        }
    }

}