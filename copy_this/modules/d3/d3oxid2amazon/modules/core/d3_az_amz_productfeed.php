<?php

/**
 *  D3 MG 2012-04-04
 * 
 *  -Portaltexte
 *  -Farbe - OL_Color
 *  -Material - OL_Material
 * 
 *  D3 MG 2012-04-18 
 *  -Browsenodes
 *  -Searchterms
 * 
 * az_amz_productfeed => d3oxid2amazon/core/d3_az_amz_feed
 */
class d3_az_amz_productfeed extends d3_az_amz_productfeed_parent
{

    protected function _getDescriptionDataXml($product)
    {
        $amzConfig = $this->_getAmzConfig();

        $sXml = '<DescriptionData>' . $this->nl;
        // DescriptionData -> Title
        $sXml .= $this->_getXmlIfExists('Title', $product->oxarticles__oxtitle->value) . $this->nl;

        // DescriptionData -> Brand
        if (isset($amzConfig->sBrandField))
        {
            if ($amzConfig->sBrandField == 'oxvendorid' && ($oVendor = $product->getVendor()))
            {
                $brand = $oVendor->oxvendor__oxtitle->value;
            }
            elseif ($amzConfig->sBrandField == 'oxmanufacturerid' && ($oManufacturer = $product->getManufacturer()))
            {
                $brand = $oManufacturer->oxmanufacturers__oxtitle->value;
            }
            else
            {
                $brand = '';
            }
            $sXml .= $this->_getXmlIfExists('Brand', $brand) . $this->nl;
        }
        // DescriptionData -> Designer
        // DescriptionData -> Description
        #$description = $this->_getXmlIfExists('Description', strip_tags($product->oxarticles__oxlongdesc->value));
        //
        //ahtportaltext
        $description = $this->_getXmlIfExists('Description', strip_tags($product->oxarticles__ahtportaltext->value));

        if (strlen($description) == 0)
        {
            $description = $this->_getXmlIfExists('Description', strip_tags($product->oxarticles__oxshortdesc->value));
        }
        $sXml .= $description . $this->nl;

        // DescriptionData -> BulletPoint (max 5)
        $aBuletPoints = explode(',', $product->oxarticles__oxshortdesc->value, 6);
        if (isset($aBuletPoints[5]))
        {
            unset($aBuletPoints[5]);
        }
        foreach ($aBuletPoints as $buletPoint)
        {
            $sXml .= $this->_getXmlIfExists('BulletPoint', $buletPoint) . $this->nl;
        }
        // DescriptionData -> ItemDimensions (Length, Width, Height, Weight)
        if (
                $product->oxarticles__oxweight->value > 0
                || $product->oxarticles__oxlength->value > 0
                || $product->oxarticles__oxheight->value > 0
                || $product->oxarticles__oxwidth->value > 0
        )
            $sXml .= '<ItemDimensions>' . $this->nl;

        if ($product->oxarticles__oxlength->value > 0)
            $sXml .= $this->_getXmlIfExists('Length', $product->oxarticles__oxlength->value, array('unitOfMeasure' => 'M')) . $this->nl;

        if ($product->oxarticles__oxlength->value > 0)
            $sXml .= $this->_getXmlIfExists('Width', $product->oxarticles__oxwidth->value, array('unitOfMeasure' => 'M')) . $this->nl;

        if ($product->oxarticles__oxheight->value > 0)
            $sXml .= $this->_getXmlIfExists('Height', $product->oxarticles__oxheight->value, array('unitOfMeasure' => 'M')) . $this->nl;

        if (
                $product->oxarticles__oxweight->value > 0
                || $product->oxarticles__oxlength->value > 0
                || $product->oxarticles__oxheight->value > 0
                || $product->oxarticles__oxwidth->value > 0
        )
            $sXml .= '</ItemDimensions>' . $this->nl;



        // DescriptionData -> PackageDimensions (Length, Width, Height)
        // DescriptionData -> <PackageWeight unitOfMeasure="{GR|KG|OZ|LB}"></PackageWeight>
        // DescriptionData -> <ShippingWeight unitOfMeasure="{GR|KG|OZ|LB}"></PackageWeight>
        // DescriptionData -> MerchantCatalogNumber
        // DescriptionData -> <MSRP currency="{USD|GBP|EUR|JPY|CAD}"></MSRP>
        if (isset($product->oxarticles__oxtprice->value) && $product->oxarticles__oxtprice->value > 0)
        {
            $aCur = oxConfig::getInstance()->getCurrencyArray($this->getDestination()->az_amz_destinations__az_currency->value);
            foreach ($aCur as $oCur)
            {
                if ($oCur->selected == 1)
                {
                    break;
                }
            }
            $sXml .= $this->_getXmlIfExists('MSRP', number_format($product->oxarticles__oxtprice->value * $oCur->rate, 2, '.', ''), array('currency' => $oCur->name)) . $this->nl;
        }

//        if(isset($product->oxarticles__oxtprice->value) && ) {
//            
//        }
        // DescriptionData -> MaxOrderQuantity 
        // DescriptionData -> SerialNumberRequired 
        // DescriptionData -> Prop65
        // DescriptionData -> <CPSIAWarning>choking_hazard_balloon|choking_hazard_contains_a_marble|choking_hazard_contains_small_ball|choking_hazard_is_a_marble|choking_hazard_is_a_small_ball|choking_hazard_small_parts|no_warning_applicable
        // DescriptionData -> CPSIAWarningDescription
        // DescriptionData -> LegalDisclaimer
        // DescriptionData -> Manufacturer

        if ($amzConfig->sManufacturerField == 'oxvendorid' && ($oVendor = $product->getVendor()))
        {
            $manufacturer = $oVendor->oxvendor__oxtitle->value;
        }
        elseif ($amzConfig->sManufacturerField == 'oxmanufacturerid' && ($oManufacturer = $product->getManufacturer()))
        {
            $manufacturer = $oManufacturer->oxmanufacturers__oxtitle->value;
        }
        else
        {
            $manufacturer = '';
        }
        //$sXml .= $this->_getXmlIfExists('Brand', $manufacturer).$this->nl;
        $sXml .= $this->_getXmlIfExists('Manufacturer', $manufacturer) . $this->nl;



        // BrowseNodes - at the moment not implemented, therefore dummy-function which can be overloaded by module
        $sXml .= $this->_getBrowseNodes($product) . $this->nl;

        //SearchTerms1
        $sXml .= $this->_getSearchTerms($product) . $this->nl;



        // DescriptionData -> MfrPartNumber
        // DescriptionData -> SearchTerms maxOccurs="5"
        
        /*
        $searchKeys = explode(' ', $product->oxarticles__oxsearchkeys->value, 6);

        for ($i = 0; $i < 5 && isset($searchKeys[$i]); ++$i)
        {
            $sXml .= $this->_getXmlIfExists('SearchTerms', $searchKeys[$i]) . $this->nl;
        }

        */


        // DescriptionData -> PlatinumKeywords maxOccurs="20"
        // DescriptionData -> Memorabilia bool
        // DescriptionData -> Autographed bool
        // DescriptionData -> UsedFor maxOccurs="5"
        // DescriptionData -> ItemType
        // DescriptionData -> OtherItemAttributes maxOccurs="5"
        // DescriptionData -> TargetAudience maxOccurs="3"
        // DescriptionData -> SubjectContent maxOccurs="5"
        // DescriptionData -> IsGiftWrapAvailable bool
        // DescriptionData -> IsGiftMessageAvailable bool
        // DescriptionData -> PromotionKeywords maxOccurs="10"
        // DescriptionData -> IsDiscontinuedByManufacturer bool
        // DescriptionData -> DeliveryChannel= in_store|direct_ship
        // DescriptionData -> MaxAggregateShipQuantity
        // DescriptionData -> RecommendedBrowseNode integer
        // DescriptionData -> FEDAS_ID

        
        //Farbe
        $sXml .= $this->_getXmlIfExists('OL_Color', $product->oxarticles__ahtfarbe->value) . $this->nl;
        //Material
        $sXml .= $this->_getXmlIfExists('OL_Material', $product->oxarticles__ahtmaterial->value) . $this->nl;

        //PrivateLabel
        $sXml .= $this->_getXmlIfExists('RegisteredParameter', "PrivateLabel") . $this->nl;

        //Quantity
        #$sXml .= $this->_getXmlIfExists('Quantity', $product->oxarticles__oxstock->value) . $this->nl;

        $sXml .= '</DescriptionData>' . $this->nl;

        return $sXml;
    }

    protected function _getBrowseNodes($product)
    {
        $aBrowsNodes = $this->_searchAmazonCategories4Article($product);
        $sRet = "";
        $iCounter = 1;
        foreach ($aBrowsNodes as $aBrowseNode)
        {
            #$sRet.= $this->_getXmlIfExists('RecommendedBrowseNode'.$iCounter, $product->oxarticles__amazon_browsenode1->value) . $this->nl;
            $sRet.= $this->_getXmlIfExists('RecommendedBrowseNode' . $iCounter, $aBrowseNode['AmazonCatID']) . $this->nl;
            $iCounter++;
        }

        return $sRet;
    }

    /**
     *
     * @param object $product
     * @return array 
     */
    protected function _searchAmazonCategories4Article($product)
    {
        $oaz_amz_categories = oxnew('az_amz_categories');
        return $oaz_amz_categories->searchAmazonCategories4Article($product);
    }

    
    /**
     * Gibt die Suchbegriffe als Searchterms zurueck
     * @param object $product
     * @return string 
     */
    protected function _getSearchTerms($product)
    {

        $aSearchTerms = $product->d3GetSearchTermsForAmazon();
        $sRet = "";
        $iCounter = 1;
        foreach ($aSearchTerms as $aSearchTerm)
        {
            #$sRet.= $this->_getXmlIfExists('RecommendedBrowseNode'.$iCounter, $product->oxarticles__amazon_browsenode1->value) . $this->nl;
            $sRet.= $this->_getXmlIfExists('SearchTerms' . $iCounter, $aSearchTerm['sSearch']) . $this->nl;
            $iCounter++;
        }
        return $sRet;
    }

}