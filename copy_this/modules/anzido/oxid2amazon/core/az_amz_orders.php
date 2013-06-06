<?php

class az_amz_orders extends oxSuperCfg
{

    protected $_sSourceDir = "";

    protected $_aReportFileNames = array();

    protected $_sCurrentFile = NULL;

    protected $_oAZConfig = NULL;

    protected $_dMaxVatPercent = 19;

    protected $_dShippingcost = 0;

    protected $_blOrderDelete = FALSE;

    public function __construct()
    {
        $this->_oAZConfig = oxNew('az_amz_config', oxConfig::getInstance()->getShopId());

        #return $ret;
    }

    /**
     * Set Name for directory
     *
     * @param type $sSourceDir
     */
    public function setSourceDir($sSourceDir = "")
    {
        $this->_sSourceDir = $this->getShopBasePath() . $sSourceDir;
    }

    /**
     * get absolut path
     *
     * @return string
     */
    public function getShopBasePath()
    {
        return $this->getConfig()->getConfigParam('sShopDir');
    }

    /**
     * Set Filename
     *
     * @param string $sFileName
     */
    public function setCurrentFileName($sFileName)
    {
        $this->_sCurrentFile = $sFileName;
    }

    public function readFiles()
    {
        $this->_readSourceDir();
        foreach ($this->_aReportFileNames as $sFileName) {
            $this->setCurrentFileName($sFileName);
            $this->_parseFileContent();
            $this->_moveOrderReport($sFileName);
        }
    }

    /**
     * Move Report xml-file
     * move to folder done
     *
     * @param string $sFileName
     */
    protected function _moveOrderReport($sFileName)
    {
        #echo __LINE__;
        if(!is_dir($this->_sSourceDir . "/done")) {
            mkdir($this->_sSourceDir . "/done");
        }
        rename($this->_sSourceDir . "/$sFileName", $this->_sSourceDir . "/done/$sFileName");
    }

    protected function _readSourceDir()
    {
        echo "<br>\n lokaler Pfad zu Dateien: " . $this->_sSourceDir;

        $handle = opendir($this->_sSourceDir);
        if($handle) {
            while (FALSE !== ($file = readdir($handle))) {
                if($file != "." && $file != ".." && substr($file, 0, 1) != "." && !is_dir(
                        $this->_sSourceDir . "/" . $file
                    )
                ) {
                    $this->_aReportFileNames[] = $file;
                    echo "<br>\n XML-Datei: " . $file;
                }
            }
            closedir($handle);
        }
        dumpvar($this->_aReportFileNames);

    }

    protected function _parseFileContent()
    {
        $xml = simplexml_load_file($this->_sSourceDir . "/" . $this->_sCurrentFile);

        //dumpVar($xml->Message[0]);

        foreach ($xml->Message as $oAmzOrder) {
            $this->_collectSingleOrderData($oAmzOrder);
        }
    }

    protected function _collectSingleOrderData($oAmzSingleOrderData)
    {
        //$oAmzOrderData		= $xml->Message->OrderReport;
        $oAmzOrderData    = $oAmzSingleOrderData->OrderReport;
        $oAmzBillingData  = $oAmzOrderData->BillingData;
        $oAmzDeliveryData = $oAmzOrderData->FulfillmentData;
        $oAmzItemData     = $oAmzOrderData->Item;

        if(empty($oAmzOrderData->AmazonOrderID)) {
            return FALSE;
        }

        $aValues = array();

        $aValues['AMZORDERID'] = $oAmzOrderData->AmazonOrderID;
        ##TODO: shop-id handling in EE
        $aValues['OXSHOPID']     = oxConfig::getInstance()->getShopId();
        $aValues['AMZORDERDATE'] = $oAmzOrderData->OrderDate;
        $aValues['BUYEREMAIL']   = $oAmzBillingData->BuyerEmailAddress;
        $aValues['BUYERNAME']    = $oAmzBillingData->BuyerName;
        $aValues['BUYERPHONE']   = $oAmzBillingData->BuyerPhoneNumber;
        ##TODO: what happens if there is a "company" name? probably then company is in field one and street in field two?
        if(empty($oAmzBillingData->Address->AddressFieldTwo)) {
            $aValues['BUYERSTREET'] = $oAmzBillingData->Address->AddressFieldOne;
        }
        else {
            $aValues['BUYERCOMPANY'] = $oAmzBillingData->Address->AddressFieldOne;
            $aValues['BUYERSTREET']  = $oAmzBillingData->Address->AddressFieldTwo;
        }
        $aValues['BUYERCITY']        = $oAmzBillingData->Address->City;
        $aValues['BUYERZIP']         = $oAmzBillingData->Address->PostalCode;
        $aValues['BUYERCOUNTRYCODE'] = $oAmzBillingData->Address->CountryCode;
        $aValues['DELSERVICELEVEL']  = $oAmzDeliveryData->FulfillmentServiceLevel;
        $aValues['DELNAME']          = $oAmzDeliveryData->Address->Name;

        if(empty($oAmzDeliveryData->Address->AddressFieldTwo)) {
            $aValues['DELSTREET'] = $oAmzDeliveryData->Address->AddressFieldOne;
        }
        else {
            $aValues['DELCOMPANY'] = $oAmzDeliveryData->Address->AddressFieldOne;
            $aValues['DELSTREET']  = $oAmzDeliveryData->Address->AddressFieldTwo;
        }

        $aValues['DELCITY']        = $oAmzDeliveryData->Address->City;
        $aValues['DELZIP']         = $oAmzDeliveryData->Address->PostalCode;
        $aValues['DELCOUNTRYCODE'] = $oAmzDeliveryData->Address->CountryCode;
        $aValues['DELPHONE']       = $oAmzDeliveryData->Address->PhoneNumber;
        $aValues['DELSTATE']       = $oAmzDeliveryData->Address->StateOrRegion;
        $aValues['AMZFILENAME']    = $this->_sCurrentFile;

        $this->_saveTmpData($aValues, "az_amz_orders_tmp");

        //echo "ID: ".$oAmzOrderData->AmazonOrderID."<br>";
        //dumpVar($oAmzItemData);
        //echo "<br><br>---------------<br>";

        $this->_processItems($oAmzOrderData->AmazonOrderID, $oAmzItemData);
    }

    protected function _processItems($sAmzOrderId, $oAmazonItemData)
    {
        foreach ($oAmazonItemData as $oItem) {
            $this->_processSingleItem($sAmzOrderId, $oItem);
        }
    }

    protected function _processSingleItem($sAmzOrderId, $oItemData)
    {
        $aItemValues                     = array();
        $aItemValues['AMZORDERID']       = $sAmzOrderId;
        $aItemValues['AMZORDERITEMCODE'] = $oItemData->AmazonOrderItemCode;
        $aItemValues['AMZSKU']           = $oItemData->SKU;
        $aItemValues['AMZTITLE']         = $oItemData->Title;
        $aItemValues['AMZQUANTITY']      = $oItemData->Quantity;
        $aItemValues['AMZTAXCODE']       = $oItemData->ProductTaxCode;

        $aItemValues = $this->_getComponentValues($aItemValues, $oItemData->ItemPrice->Component);

        $this->_saveTmpData($aItemValues, "az_amz_orderitems_tmp");
    }

    /**
     * @param $aItemValues
     * @param $aComponent
     *
     * @return mixed
     */
    protected function _getComponentValues($aItemValues, $aComponent)
    {
        foreach ($aComponent as $oPrice) {
            switch ($oPrice->Type) {
                case "Principal":
                    $aItemValues['AMZARTPRICE'] = $oPrice->Amount;
                    break;
                case "Shipping":
                    $aItemValues['AMZSHIPPRICE'] = $oPrice->Amount;
                    break;
                case "Tax":
                    $aItemValues['AMZARTTAX'] = $oPrice->Amount;
                    break;
                case "ShippingTax":
                    $aItemValues['AMZSHIPTAX'] = $oPrice->Amount;
                    break;

                default:
                    break;
            }
        }
        return $aItemValues;
    }

    /**
     * @param $aValues
     * @param $sTable
     */
    protected function _saveTmpData($aValues, $sTable)
    {
        $sInsert = "insert into $sTable set ";

        foreach ($aValues as $sField => $sValue) {
            $aPart[] = "$sField = '" . utf8_decode($sValue) . "'";
        }
        $sInsert .= implode(", ", $aPart);
        $sInsert .= ", AZTIMESTAMP = '" . date("Y-m-d H:i:s") . "'";
        #echo $sInsert."<br>";
        oxDb::getDb()->Execute($sInsert);
    }

    public function importOrders()
    {
        $aOrders = $this->_getOrders();
        foreach ($aOrders as $aOrder) {
            if(!empty($aOrder)) {
                $sUserId = $this->_d3getOrderUser($aOrder);
                $oUser   = oxnew('oxuser');
                $oUser->load($sUserId);

                $oAdress = $this->_d3getDelAdresse($aOrder);

                /* @var $oOrder oxorder */
                $oOrder                       = oxNew("oxorder");
                $oOrder->oxorder__amzorderid  = oxnew("oxField", $aOrder['AMZORDERID']);
                $oOrder->oxorder__oxshopid    = oxnew("oxField", $aOrder['OXSHOPID']);
                $oOrder->oxorder__oxuserid    = oxnew("oxField", $oUser->getId());
                $oOrder->oxorder__oxbillemail = oxnew("oxField", $aOrder['BUYEREMAIL']);
                $oOrder->oxorder__oxbilllname = oxnew("oxField", $oUser->getFieldData('OXLNAME'));
                #$oOrder->oxorder__oxbilllname = oxnew("oxField", $oUser->oxuser__oxbilllname->rawValue);
                $oOrder->oxorder__oxbillfname = oxnew("oxField", $oUser->getFieldData('OXFNAME'));
                #$oOrder->oxorder__oxbillfname = oxnew("oxField", $oUser->oxuser__oxbillfname->rawValue);
                $oOrder->oxorder__oxbillstreet    = oxnew("oxField", $aOrder['BUYERSTREET']);
                $oOrder->oxorder__oxbillcity      = oxnew("oxField", $aOrder['BUYERCITY']);
                $oOrder->oxorder__oxbillzip       = oxnew("oxField", $aOrder['BUYERZIP']);
                $oOrder->oxorder__oxbillcountryid = oxnew(
                    "oxField",
                    $this->_getUserCountry($aOrder['BUYERCOUNTRYCODE'])
                );
                $oOrder->oxorder__oxbillfon       = oxnew("oxField", $aOrder['BUYERPHONE']);
                #$oOrder->oxorder__oxdelfname = oxnew("oxField", $oUser->getFieldData('OXDELFNAME'));
                $oOrder->oxorder__oxdelfname = oxnew("oxField", $oAdress->oxaddress__oxfname->rawValue);
                #$oOrder->oxorder__oxdellname = oxnew("oxField", $oUser->getFieldData('OXDELLNAME'));
                $oOrder->oxorder__oxdellname     = oxnew("oxField", $oAdress->oxaddress__oxfname->rawValue);
                $oOrder->oxorder__oxdelcompany   = oxnew("oxField", $aOrder['DELCOMPANY']);
                $oOrder->oxorder__oxdelstreet    = oxnew("oxField", $aOrder['DELSTREET']);
                $oOrder->oxorder__oxdelcity      = oxnew("oxField", $aOrder['DELCITY']);
                $oOrder->oxorder__oxdelzip       = oxnew("oxField", $aOrder['DELZIP']);
                $oOrder->oxorder__oxdelcountryid = oxnew("oxField", $this->_getUserCountry($aOrder['DELCOUNTRYCODE']));
                $oOrder->oxorder__oxdelfon       = oxnew("oxField", $aOrder['DELPHONE']);
                $oOrder->oxorder__oxfolder       = oxnew("oxField", "ORDERFOLDER_NEW");
                $oOrder->oxorder__oxdeltype      = oxnew(
                    "oxField",
                    $this->_getAmazonShipSet($aOrder['DELSERVICELEVEL'])
                );
                $oOrder->oxorder__oxpaymenttype  = oxnew("oxField", $this->_getAmazonPayment());
                $oOrder->oxorder__oxcurrency     = oxnew("oxField", "EUR");
                $oOrder->oxorder__oxcurrate      = oxnew("oxField", 1);
                $oOrder->oxorder__oxtransstatus  = oxnew("oxField", "OK");

                //Nr ebenfalls in OXTRANSID abspeichern
                $oOrder->oxorder__oxtransid = oxnew("oxField", $aOrder['AMZORDERID']);
                $oOrder->save();
                $sOrderId    = $oOrder->getId();
                $dArticleSum = $this->_saveOrderArticles($aOrder['AMZORDERID'], $sOrderId);

                // if there are any errors on inserting order articles $this->_blOrderDelete will be set to true
                // this means: delete order and return immediately, do not set order sum - cause order is saved again there
                // DOC: such deleted orders stay in tmp tables with azprocessed flag = 0. they could be imported later.
                // TODO: clean up tmp tables

                if($this->_blOrderDelete) {
                    $this->_deleteOrder($sOrderId);
                    return;
                }
                $this->_setOrderSum($oOrder, $dArticleSum);

                $this->_setOrderDone($aOrder['AMZORDERID']);

                $this->_D3setOrderDate($oOrder, $aOrder['AMZORDERDATE']);

                //E-Mail - Kunde + Admin
                $this->_SendOrderByEmail($oOrder->getId());
            }
        }
    }

    /**
     * @param string $sOrderId
     */
    protected function _deleteOrder($sOrderId)
    {
        oxDb::getDb()->Execute("delete from oxorder where oxid = '$sOrderId'");
        oxDb::getDb()->Execute("delete from oxorderarticles where oxorderid = '$sOrderId'");
        $this->_blOrderDelete = FALSE;
    }

    /**
     * @param string $sAmzOrderId
     */
    protected function _setOrderDone($sAmzOrderId)
    {
        oxDb::getDb()->Execute(
            "update az_amz_orders_tmp set dateofimport = '" . date("Y-m-d H:i:s") . "', azprocessed = '1' where amzorderid = '$sAmzOrderId'"
        );
    }

    /**
     * @param object $oOrder
     * @param float  $dArticleSum
     */
    protected function _setOrderSum($oOrder, $dArticleSum)
    {
        $oOrder->oxorder__oxtotalbrutsum->value  = $dArticleSum;
        $aVatValues                              = $this->_getNetPrice($dArticleSum, $this->_dMaxVatPercent);
        $oOrder->oxorder__oxtotalnetsum->value   = $aVatValues['dNetPrice'];
        $oOrder->oxorder__oxtotalordersum->value = $dArticleSum + $this->_dShippingcost;
        $oOrder->oxorder__oxdelcost->value       = $this->_dShippingcost;
        $oOrder->save();
    }

    /**
     * @param object $oOrder
     * @param date   $sDate
     */
    protected function _setOrderDate($oOrder, $sDate)
    {
        #echo "<br>Datum: ".$sDate;
        #echo "<br>".__FUNCTION__." :: ".__LINE__;
        #$oOrder->oxorder__oxorderdate->value = $sDate; 
        $sId = $oOrder->getId();

        $oDb = oxDb::getDb();

        $sUpdate = "UPDATE oxorder SET oxorderdate=" . $oDb->quote($sDate) . " WHERE oxid=" . $oDb->quote($sId);
        #echo "<br>".$sUpdate;

        $oDb->execute($sUpdate);
        #$oOrder->assign(array('oxorderdate' =>$sDate));
        #$oOrder->save();        
    }

    protected function _getAmazonPayment()
    {
        return $this->_oAZConfig->sAmazonPayment;
    }

    /**
     * @param string $sAmzServiceLevel
     *
     * @return mixed
     */
    protected function _getAmazonShipSet($sAmzServiceLevel)
    {
        switch ($sAmzServiceLevel) {
            case "Standard":
                return $this->_oAZConfig->sAmazonShippingStandard;
                break;
            case "Expedited":
                return $this->_oAZConfig->sAmazonShippingExpress;
                break;
            default:
                return $this->_oAZConfig->sAmazonShippingStandard;
                break;
        }
    }

    /**
     * @param $sAmzOrderId
     * @param $sOrderId
     *
     * @return int
     */
    protected function _saveOrderArticles($sAmzOrderId, $sOrderId)
    {

        $dArticleSum          = 0;
        $this->_dShippingcost = 0;
        $aVatPercent          = array();

        $aOrderItems = $this->_getOrderArticles($sAmzOrderId);
        //dumpVar($aOrderItems);
        foreach ($aOrderItems as $aOrderItem) {
            /* @var $oOrderArticle oxorderarticle */
            $oOrderArticle = oxNew("oxorderarticle");
            $oArticle      = $this->_getOrderArticle($aOrderItem['AMZSKU']);

            if(!$oArticle) {
                //die('article with SKU ' . $aOrderItem['AMZSKU'] . ' not found');
                $this->_oAZConfig->logError(
                    "order import error: article with SKU " . $aOrderItem['AMZSKU'] . " not found in article table\n"
                );
                $this->_blOrderDelete = TRUE;
                continue;
            }

            if($oArticle->oxarticles__oxvat->value > 0) {
                $dVatPercent = $oArticle->oxarticles__oxvat->value;
            }
            else {
                $dVatPercent = $this->getConfig()->getShopConfVar('dDefaultVAT');
            }

            $aNetValues = $this->_getNetPrice($aOrderItem['AMZARTPRICE'], $dVatPercent);
            if(!in_array($dVatPercent, $aVatPercent)) {
                $aVatPercent[] = $dVatPercent;
            }

            $oOrderArticle->oxorderarticles__oxartid->value      = $oArticle->getId();
            $oOrderArticle->oxorderarticles__oxartnum->value     = $oArticle->oxarticles__oxartnum->value;
            $oOrderArticle->oxorderarticles__oxtitle->value      = $oArticle->oxarticles__oxtitle->value;
            $oOrderArticle->oxorderarticles__oxshortdesc->value  = $oArticle->oxarticles__oxshortdesc->value;
            $oOrderArticle->oxorderarticles__oxselvariant->value = $oArticle->oxarticles__oxvarselect->value;

            $oOrderArticle->oxorderarticles__oxorderid->value   = $sOrderId;
            $oOrderArticle->oxorderarticles__oxamount->value    = $aOrderItem['AMZQUANTITY'];
            $oOrderArticle->oxorderarticles__oxnetprice->value  = $aNetValues['dNetPrice'];
            $oOrderArticle->oxorderarticles__oxbrutprice->value = $aOrderItem['AMZARTPRICE'];
            $dArticleSum += $aOrderItem['AMZARTPRICE'];
            $oOrderArticle->oxorderarticles__oxvatprice->value = $aNetValues['dVatPrice'];
            $this->_dShippingcost += $aOrderItem['AMZSHIPPRICE'];

            /** Add D3 MG/TD START 2011_05_30  * */
            /* Artikel in oxorderarticles werden sonst "sporadisch ohne Vat und oxprice gespeichert" */
            $oOrderArticle->oxorderarticles__oxvat->value    = $dVatPercent;
            $oOrderArticle->oxorderarticles__oxprice->value  = $aOrderItem['AMZARTPRICE'] / $aOrderItem['AMZQUANTITY'];
            $oOrderArticle->oxorderarticles__oxbprice->value = $aOrderItem['AMZARTPRICE'] / $aOrderItem['AMZQUANTITY'];
            $oOrderArticle->oxorderarticles__oxnprice->value = $aNetValues['dNetPrice'] / $aOrderItem['AMZQUANTITY'];
            /** Add D3 MG/TD  * */
            $oOrderArticle->save();

            /* ADD D3 MG 2012-05-03 - Daten manuell an Artikel speichern */
            $this->_addParams2OrderArticle($oOrderArticle->getId(), $oArticle);

            // TODO for EE 2.7: write alternative function for updateArticleStock
            $oOrderArticle->updateArticleStock(
                $oOrderArticle->oxorderarticles__oxamount->value * (-1),
                $this->getConfig()->getConfigParam('blAllowNegativeStock')
            );
        }

        /* ADD D3 MG 2011_11_28 bricht sonst ab */
        if($this->_blOrderDelete == TRUE) {
            return 0;
        }

        $this->_dMaxVatPercent = max($aVatPercent);

        return $dArticleSum;
    }

    /**
     * @param $dBrutPrice
     * @param $dVatPercent
     *
     * @return array
     */
    protected function _getNetPrice($dBrutPrice, $dVatPercent)
    {
        $aNetValues              = array();
        $dVat                    = round($dBrutPrice - ($dBrutPrice * 100 / ($dVatPercent + 100)), 12);
        $dNetPrice               = $dBrutPrice - $dVat;
        $aNetValues['dVatPrice'] = $dVat;
        $aNetValues['dNetPrice'] = $dNetPrice;

        return $aNetValues;
    }

    /**
     * @param $sAmzSku
     *
     * @return null|oxarticle
     */
    protected function _getOrderArticle($sAmzSku)
    {
        $sSkuField = $this->_oAZConfig->sSkuField;

        $sArtOxid = oxDb::getDb()->getOne("select oxid from oxarticles where $sSkuField = '$sAmzSku'");
        if(!empty($sArtOxid)) {
            /* @var $oArticle oxarticle */
            $oArticle = oxNew("oxarticle");
            $oArticle->load($sArtOxid);
            return $oArticle;
        }
        else {
            return NULL;
        }
    }

    /**
     * @param $sAmzOrderId
     *
     * @return mixed
     */
    protected function _getOrderArticles($sAmzOrderId)
    {
        $sSelect     = "select * from az_amz_orderitems_tmp where amzorderid = '$sAmzOrderId'";
        $aOrderItems = oxDb::getDb(TRUE)->getAll($sSelect);
        return $aOrderItems;
    }

    /**
     * @param $aAmzOrder
     *
     * @return oxuser
     */
    protected function _getOrderUser($aAmzOrder)
    {
        $sUserId = oxDb::getDb()->getOne(
            "select oxid from oxuser where oxusername = '" . $aAmzOrder['BUYEREMAIL'] . "'"
        );
        if(empty($sUserId)) {
            /* @var $oUser oxuser */
            $oUser = oxNew("oxuser");
            // TODO EE 2.7: oxactive -> oxactiv
            $oUser->oxuser__oxactive->value    = '1';
            $oUser->oxuser__oxusername->value  = $aAmzOrder['BUYEREMAIL'];
            $oUser->oxuser__oxlname->value     = $aAmzOrder['BUYERNAME'];
            $oUser->oxuser__oxfon->value       = $aAmzOrder['BUYERPHONE'];
            $oUser->oxuser__oxstreet->value    = $aAmzOrder['BUYERSTREET'];
            $oUser->oxuser__oxcity->value      = $aAmzOrder['BUYERCITY'];
            $oUser->oxuser__oxzip->value       = $aAmzOrder['BUYERZIP'];
            $oUser->oxuser__oxcountryid->value = $this->_getUserCountry($aAmzOrder['BUYERCOUNTRYCODE']);
            $oUser->save();
        }
        else {
            $oUser = oxNew("oxuser");
            $oUser->load($sUserId);
        }
        return $oUser;
    }

    /**
     * Add item to oxadresse
     *
     * @param array $aAmzOrder
     *
     * @return object
     */
    protected function _getDelAdresse($aAmzOrder)
    {
        $sUserId = oxDb::getDb()->getOne(
            "select oxid from oxuser where oxusername = '" . $aAmzOrder['BUYEREMAIL'] . "'"
        );

        /* @var $oAddress oxaddress */
        $oAddress    = oxNew("oxaddress");
        $sAdressOxid = $this->_GetAdressOxid($sUserId);

        if(!$oAddress->load($sAdressOxid)) {
            $oAddress->oxaddress__oxfname     = oxnew('oxfield', '');
            $oAddress->oxaddress__oxlname     = oxnew('oxfield', $aAmzOrder['DELNAME']);
            $oAddress->oxaddress__oxsal       = oxnew('oxfield', '');
            $oAddress->oxaddress__oxfon       = oxnew('oxfield', $aAmzOrder['DELPHONE']);
            $oAddress->oxaddress__oxcompany   = oxnew('oxfield', $aAmzOrder['DELCOMPANY']);
            $oAddress->oxaddress__oxstreet    = oxnew('oxfield', $aAmzOrder['DELSTREET']);
            $oAddress->oxaddress__oxcity      = oxnew('oxfield', $aAmzOrder['DELCITY']);
            $oAddress->oxaddress__oxzip       = oxnew('oxfield', $aAmzOrder['DELZIP']);
            $oAddress->oxaddress__oxaddinfo   = oxnew('oxfield', '');
            $oAddress->oxaddress__oxstateid   = oxnew('oxfield', '');
            $oAddress->oxaddress__oxcountryid = oxnew('oxfield', $this->_getUserCountry($aAmzOrder['DELCOUNTRYCODE']));
            $oAddress->save();
        }
        else {
            $oAddress->load($sAdressOxid);
        }
        return $oAddress;
    }

    /**
     * @param $sUserId
     *
     * @return mixed
     */
    protected function _GetAdressOxid($sUserId)
    {
        $oDB             = oxDb::getDb();
        $sTableoxaddress = getViewName('oxaddress');
        $sQuery          = "SELECT oxid FROM " . $sTableoxaddress . " WHERE oxuserid =" . $oDB->quote($sUserId);
        return $oDB->getOne($sQuery);
    }

    /**
     * @param $sCountryCode
     *
     * @return mixed
     */
    protected function _getUserCountry($sCountryCode)
    {
        $sCountryId = oxDb::getDb()->GetOne("select oxid from oxcountry where oxisoalpha2 = '$sCountryCode'");
        return $sCountryId;
    }

    /**
     * @param bool $blDone
     *
     * @return mixed
     */
    protected function _getOrders($blDone = FALSE)
    {
        $sSelect = "select * from az_amz_orders_tmp where azprocessed = '0'";
        if($blDone) {
            $sSelect .= " and dateofimport > 0";
        }

        #echo "<br>\n".$sSelect;
        $aOrders = oxDb::getDb(TRUE)->GetAll($sSelect);
        return $aOrders;
    }

    /**
     * @param bool $blDone
     *
     * @return mixed
     */
    protected function _getOrdersFiles($blDone = FALSE)
    {
        $sSelect = "select * from az_amz_orders_tmp where azprocessedfile = '0'";
        if($blDone) {
            $sSelect .= " and dateofimport > 0";
        }

        #echo "<br>\n".$sSelect;
        $aOrders = oxDb::getDb(TRUE)->GetAll($sSelect);
        return $aOrders;
    }

    /**
     * Delete Files from AMTU-Server
     *
     * @param string $sDestinationId
     */
    public function deleteFilesFromAMTU($sDestinationId)
    {
        $oDestination = & oxNew('az_amz_destination');
        $oDestination->load($sDestinationId);

        /* @var $oFtp az_amz_ftp */
        $oFtp      = oxNew('az_amz_ftp');
        $blSuccess = $oFtp->connect(
            $oDestination->az_amz_destinations__az_server->value,
            $oDestination->az_amz_destinations__az_ftpuser->value,
            $oDestination->az_amz_destinations__az_ftppassword->value,
            $oDestination->az_amz_destinations__az_ftppassivemode->value
        );
        if($blSuccess) {
            $aOrdersDone = $this->_getOrdersFiles(TRUE);
            #dumpVar($aOrdersDone);
            foreach ($aOrdersDone as $aOrder) {
                echo "<br>\nDatei zum loeschen: " . $aOrder['AMZFILENAME'];
                $blOk = $oFtp->deleteFile(
                    $aOrder['AMZFILENAME'],
                    $oDestination->az_amz_destinations__az_reportsdirectory->value
                );
                if($blOk) {
                    echo "<br>\nKonnte Datei loeschen:" . $aOrder['AMZFILENAME'];
                    $sDelete = "update az_amz_orders_tmp set azprocessedfile = '1' where amzfilename = '" . $aOrder['AMZFILENAME'] . "'";
                    #echo "<br>\n".$sDelete;
                    oxDb::getDb()->Execute($sDelete);
                }
                else {
                    echo "<br>\nKonnte Datei nicht loeschen:" . $aOrder['AMZFILENAME'];
                }
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
        $sDate      = date('Y-m-d H:i:s', $oUtilsDate->getTime());
        $sId        = $oOrder->getId();
        $oDb        = oxDb::getDb();
        $sUpdate    = "UPDATE oxorder SET oxorderdate=" . $oDb->quote($sDate) . " WHERE oxid=" . $oDb->quote($sId);
        #echo "<br>" . $sUpdate;

        $oDb->execute($sUpdate);
    }

    protected function _d3getOrderUser($aAmzOrder)
    {
        $sId     = '';
        $sUserId = oxDb::getDb()->getOne(
            "select oxid from oxuser where oxusername = '" . $aAmzOrder['BUYEREMAIL'] . "'"
        );
        if(empty($sUserId)) {

            $aName   = $this->_separateAmazonName($aAmzOrder['BUYERNAME']);
            $aStreet = $this->_separateAmazonStreet($aAmzOrder['BUYERSTREET']);

            /* @var $oUser oxuser */
            $oUser                             = oxNew("oxuser");
            $oUser->oxuser__oxactive->value    = '1';
            $oUser->oxuser__oxusername->value  = $aAmzOrder['BUYEREMAIL'];
            $oUser->oxuser__oxlname->value     = $aName['lname'];
            $oUser->oxuser__oxfname->value     = $aName['fname'];
            $oUser->oxuser__oxfon->value       = $aAmzOrder['BUYERPHONE'];
            $oUser->oxuser__oxstreet->value    = $aStreet['street'];
            $oUser->oxuser__oxstreetnr->value  = $aStreet['streetnr'];
            $oUser->oxuser__oxcity->value      = $aAmzOrder['BUYERCITY'];
            $oUser->oxuser__oxzip->value       = $aAmzOrder['BUYERZIP'];
            $oUser->oxuser__oxcountryid->value = $this->_getUserCountry($aAmzOrder['BUYERCOUNTRYCODE']);
            $oUser->save();
            $sId = $oUser->getId();
        }
        else {
            #$oUser = oxNew("oxuser");
            #$oUser->load($sUserId);
            $sId = $sUserId;
        }

        return $sId;
    }

    /**
     * @param array $aAmzOrder
     *
     * @return object oxaddress
     */
    protected function _d3getDelAdresse($aAmzOrder)
    {
        $sUserId = oxDb::getDb()->getOne(
            "select oxid from oxuser where oxusername = '" . $aAmzOrder['BUYEREMAIL'] . "'"
        );

        /* @var $oAddress oxaddress */
        $oAddress    = oxNew("oxaddress");
        $sAdressOxid = $this->_GetAdressOxid($sUserId);

        if(!$oAddress->load($sAdressOxid)) {
            $aName   = $this->_separateAmazonName($aAmzOrder['DELNAME']);
            $aStreet = $this->_separateAmazonStreet($aAmzOrder['DELSTREET']);

            $oAddress->oxaddress__oxfname     = oxnew('oxfield', $aName['fname']);
            $oAddress->oxaddress__oxlname     = oxnew('oxfield', $aName['lname']);
            $oAddress->oxaddress__oxsal       = oxnew('oxfield', '');
            $oAddress->oxaddress__oxfon       = oxnew('oxfield', $aAmzOrder['DELPHONE']);
            $oAddress->oxaddress__oxcompany   = oxnew('oxfield', $aAmzOrder['DELCOMPANY']);
            $oAddress->oxaddress__oxstreet    = oxnew('oxfield', $aStreet['street']);
            $oAddress->oxaddress__oxstreetnr  = oxnew('oxfield', $aStreet['streetnr']);
            $oAddress->oxaddress__oxcity      = oxnew('oxfield', $aAmzOrder['DELCITY']);
            $oAddress->oxaddress__oxzip       = oxnew('oxfield', $aAmzOrder['DELZIP']);
            $oAddress->oxaddress__oxaddinfo   = oxnew('oxfield', '');
            $oAddress->oxaddress__oxstateid   = oxnew('oxfield', '');
            $oAddress->oxaddress__oxcountryid = oxnew('oxfield', $this->_getUserCountry($aAmzOrder['DELCOUNTRYCODE']));
            $oAddress->save();
        }
        else {
            $oAddress->load($sAdressOxid);
        }
        return $oAddress;
    }

    /**
     * Add some paramter to orderarticle
     * oxinsert, oxthumb, oxpic1-3
     * oxsubclass
     * oxodershopid
     *
     * @param string $sOxid
     * @param object $oArticle
     */
    protected function _addParams2OrderArticle($sOxid, $oArticle)
    {
        $oDb     = oxDb::getDb();
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
     *
     * @return array
     */
    protected function _separateAmazonName($sName)
    {
        $aName = array();

        $sString = trim($sName);
        $iLength = strlen($sString);

        $iPos = strrpos($sString, " ");

        if($iPos) {
            $aName['fname'] = substr($sString, 0, $iPos);
            $aName['lname'] = substr($sString, ($iLength - $iPos - 1) * -1);
        }

        return $aName;
    }

    /**
     * separate Adress from Amazon
     *
     * @param string $sStreet
     *
     * @return array
     */
    protected function _separateAmazonStreet($sStreet)
    {
        $sString = trim($sStreet);
        $iLength = strlen($sString);

        $iPos  = strrpos($sString, " ");
        $iPos2 = strrpos($sString, ".");

        $aStreet = array();

        if($iPos) {
            $aStreet['street']   = substr($sString, 0, $iPos);
            $aStreet['streetnr'] = substr($sString, ($iLength - $iPos - 1) * -1);
        }
        elseif($iPos2) {
            $aStreet['street']   = substr($sString, 0, $iPos2);
            $aStreet['streetnr'] = substr($sString, ($iLength - $iPos2 - 1) * -1);
        }
        else {
            $aStreet['street']   = $sString;
            $aStreet['streetnr'] = "";
        }

        return $aStreet;
    }

    /**
     * Send E-Mail to Owner and Customer
     * Set LangId
     *
     * @param string $sOxid
     *
     * @return bool
     */
    protected function _SendOrderByEmail($sOxid)
    {
        $blOrderSend = FALSE;
        /* @var $oOrder oxorder */
        $oOrder      = oxnew('oxorder');
        $blOrderLoad = $oOrder->load($sOxid);

        if(!$blOrderLoad) {
            return $blOrderSend;
        }

        $oBasket = $oOrder->d3getOrderBasket();
        $oOrder->d3addOrderArticlesToBasket($oBasket, $oOrder->getOrderArticles(TRUE));
        $oBasket->d3calculateBasket4Amazon();
        #$oBasket->calculateBasket();

        $oUser        = $oOrder->getOrderUser();
        $oUserPayment = $oOrder->d3setPayment4Amazon($oBasket->getPaymentId());

        //LangId für Email
        $oLanguages = oxLang::getInstance();
        $iBaseLang  = $oLanguages->getBaseLanguage();
        $iLang      = $oOrder->getFieldData('oxlang');
        $oLanguages->setBaseLanguage($iLang);

        $blOrderSend = $oOrder->d3sendOrderByEmail($oUser, $oBasket, $oUserPayment);
        //Restore Language
        $oLanguages->setBaseLanguage($iBaseLang);

        return $blOrderSend;
    }

    public function readFiles()
    {
        $this->_readSourceDir();
        foreach ($this->_aReportFileNames as $sFileName) {
            $this->setCurrentFileName($sFileName);
            $this->_parseFileContent();
            $this->_moveOrderReport($sFileName);
        }
    }

}