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
 * 
 *  az_amz_productfeed => d3oxid2amazon/feeds/d3_amz_productfeed
 */
class d3_amz_productfeed extends d3_amz_productfeed_parent
{

    public function getUpdateXml($id)
    {
        $amzConfig = $this->_getAmzConfig();

        $product = $this->_getProduct($id);

        $sXml = '<Message>' . $this->nl;
        $sXml .= '<MessageID>' . (++$this->_messageId) . '</MessageID>' . $this->nl;
        #$sXml .= '<OperationType>PartialUpdate</OperationType>' . $this->nl;
        $sXml .= '<OperationType>Update</OperationType>' . $this->nl;

        $sSkuProp = $this->getSkuProperty();
        $sSkuValue = $this->cutEanNumber($product->$sSkuProp->value);

        /*
          $sXml .= '<Inventory>' . $this->nl;
          $sXml .= $this->_getXmlIfExists('SKU', $sSkuValue) . $this->nl;
          $sXml .= $this->_getXmlIfExists('Available', 'true') . $this->nl;
          $sXml .= '</Inventory>' . $this->nl;
         */
        $sXml .= '<Product>' . $this->nl;


        /*
          if($this->azHasAnyVariant($product)) {
          $sSkuValue = "P".$sSkuValue;
          }
         */
        $sXml .= '<SKU>' . $sSkuValue . '</SKU>';


        if (!$this->azHasAnyVariant($product))
        {
            #$sEanField = 'oxarticles__' . $amzConfig->sEanField;
            #$sXml .= '<StandardProductID>' . $this->nl;
            #$sXml .= '<Type>EAN</Type>' . $this->nl;
            #$sXml .= '<Value>' . $product->$sEanField->value . '</Value>' . $this->nl;
            #$sXml .= '<Value>' . $sSkuValue . '</Value>' . $this->nl;
            #$sXml .= '</StandardProductID>' . $this->nl;
        }

        #$sXml .= '<LaunchDate>' . date("Y-m-d") . "T" . date("H:i:s") . "+01:00" . '</LaunchDate>' . $this->nl;
        // ProductTaxCode
        // LaunchDate
        // DiscontinueDate
        // ReleaseDate
        // Condition ConditionType = 
        //[New, UsedLikeNew, UsedVeryGood, UsedGood, UsedAcceptable, CollectibleLikeNew, 
        //CollectibleVeryGood, CollectibleGood, CollectibleAcceptable, Refurbished, Club]

        /*
          if (isset($amzConfig->sConditionTypeField))
          {
          $sConditionTypeField = $amzConfig->sConditionTypeField;
          }
          $sXml .= '<Condition>' . $this->nl;
          $possibleConditions = array('New', 'UsedLikeNew', 'UsedVeryGood',
          'UsedGood', 'UsedAcceptable', 'CollectibleLikeNew',
          'CollectibleVeryGood', 'CollectibleGood', 'CollectibleAcceptable',
          'Refurbished', 'Club');
          if (!empty($sConditionTypeField) && $sConditionTypeField != 'new' && isset($product->{'oxarticles__' . $sConditionTypeField}))
          {
          $sConditionType = $product->{'oxarticles__' . $sConditionTypeField}->value;
          if (!in_array($sConditionType, $possibleConditions))
          {
          unset($sConditionType);
          }
          }
          if (!isset($sConditionType))
          {
          $sConditionType = '<ConditionType>New</ConditionType>' . $this->nl;
          }
          $sXml .= $sConditionType;

          // Condition ConditionNote
          $sXml .= '</Condition>' . $this->nl;
         */



        // Rebate (RebateStartDate, RebateEndDate, RebateMessage, RebateName)
        // ItemPackageQuantity
        // NumberOfItems

        $sXml .= $this->_getDescriptionDataXml($product);
        $sXml .= $this->_getProductDataXml($product);

        $sXml .= '</Product>';
        $sXml .= '</Message>';
        return $sXml;
    }

    protected function _getDescriptionDataXml($product)
    {
        $amzConfig = $this->_getAmzConfig();

        $sXml = '<DescriptionData>' . $this->nl;
        // DescriptionData -> Title
        $sXml .= $this->_getXmlIfExists('Title', trim($product->oxarticles__oxtitle->value)) . $this->nl;

        // DescriptionData -> Brand
        #if (isset($amzConfig->sBrandField))
        # {
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
            $brand = 'OUTFLEXX';
        }
        $sXml .= $this->_getXmlIfExists('Brand', $brand) . $this->nl;
        #}


        // DescriptionData -> Designer
        // DescriptionData -> Description
        #$description = $this->_getXmlIfExists('Description', strip_tags($product->oxarticles__oxlongdesc->value));
        //
        //ahtportaltext
        #$description = $product->getFieldData('AHTPORTALTEXT');
        $description = $product->oxarticles__ahtportaltext->rawValue;
        #$description = $product->oxarticles__AHTPORTALTEXT->rawValue;

        if (strlen($description) == 0)
        {
            $description = $product->oxarticles__oxshortdesc->value;
        }

        #$description = str_replace ('&reg;','&#xae;',$description);
        #$description = str_replace('<br>', PHP_EOL, $description);
        #$sXml .= $description . $this->nl;
        #$sXml .= $this->_getXmlIfExists('Description', strip_tags($description)) . $this->nl;
        $sXml .= $this->_getXmlIfExistsRaw('Description', $description) . $this->nl;

        // DescriptionData -> BulletPoint (max 5)
        /*
         * D3 MG 2012-05-07
          $aBuletPoints = explode(',', $product->oxarticles__oxshortdesc->value, 6);
          if (isset($aBuletPoints[5]))
          {
          unset($aBuletPoints[5]);
          }
          foreach ($aBuletPoints as $buletPoint)
          {
          $sXml .= $this->_getXmlIfExists('BulletPoint', $buletPoint) . $this->nl;
          }
         */

        // D3 MG 2012-05-07 ADD BulletPoint1
        $sXml .= $this->_getXmlIfExists('BulletPoint', $product->oxarticles__d3amazonbulletpoint1->value) . $this->nl;
        $sXml .= $this->_getXmlIfExists('BulletPoint', $product->oxarticles__d3amazonbulletpoint2->value) . $this->nl;
        $sXml .= $this->_getXmlIfExists('BulletPoint', $product->oxarticles__d3amazonbulletpoint3->value) . $this->nl;
        $sXml .= $this->_getXmlIfExists('BulletPoint', $product->oxarticles__d3amazonbulletpoint4->value) . $this->nl;
        $sXml .= $this->_getXmlIfExists('BulletPoint', $product->oxarticles__d3amazonbulletpoint5->value) . $this->nl;
    
        

        // DescriptionData -> ItemDimensions (Length, Width, Height, Weight)
        if ($product->oxarticles__oxweight->value > 0
                || $product->oxarticles__oxlength->value > 0
                || $product->oxarticles__oxheight->value > 0
                || $product->oxarticles__oxwidth->value > 0
        )
        {
            $sXml .= '<ItemDimensions>' . $this->nl;

            if ($product->oxarticles__oxlength->value > 0)
                $sXml .= $this->_getXmlIfExists('Length', round($product->oxarticles__oxlength->value, 2), array('unitOfMeasure' => 'M')) . $this->nl;

            if ($product->oxarticles__oxlength->value > 0)
                $sXml .= $this->_getXmlIfExists('Width', round($product->oxarticles__oxwidth->value, 2), array('unitOfMeasure' => 'M')) . $this->nl;

            if ($product->oxarticles__oxheight->value > 0)
                $sXml .= $this->_getXmlIfExists('Height', round($product->oxarticles__oxheight->value, 2), array('unitOfMeasure' => 'M')) . $this->nl;

            $sXml .= '</ItemDimensions>' . $this->nl;
        }

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
            $manufacturer = 'OUTFLEXX'; 
        }
        //$sXml .= $this->_getXmlIfExists('Brand', $manufacturer).$this->nl;
        $sXml .= $this->_getXmlIfExists('Manufacturer', $manufacturer) . $this->nl;           
        
        //SearchTerms1
        // DescriptionData -> SearchTerms maxOccurs="5"
        /*
          $searchKeys = explode(' ', $product->oxarticles__oxsearchkeys->value, 6);
          for ($i = 0; $i < 5 && isset($searchKeys[$i]); ++$i)
          {
          $sXml .= $this->_getXmlIfExists('SearchTerms', $searchKeys[$i]) . $this->nl;
          }
         */
        
        $sXml .= $this->_getSearchTerms($product);

        // BrowseNodes - at the moment not implemented, therefore dummy-function which can be overloaded by module
        $sXml .= $this->_getBrowseNodes($product);

        // DescriptionData -> MfrPartNumber
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
        // 
        //PrivateLabel
        //Quantity
        #$sXml .= $this->_getXmlIfExists('Quantity', $product->oxarticles__oxstock->value) . $this->nl;

        $sXml .= '</DescriptionData>' . $this->nl . $this->nl;
        return $sXml;
    }

    protected function _getProductDataXml($product)
    {
        $sXml = '';
        $sXml = '<ProductData>' . $this->nl;
        $sXml .= '<Home>' . $this->nl;
        $sXml .= '<ProductType>' . $this->nl;
        $sXml .= '<OutdoorLiving>' . $this->nl;        //Farbe
        $sXml .= $this->_getXmlIfExists('Material', $product->oxarticles__ahtmaterial->value) . $this->nl;
        $sXml .= '<VariationData>' . $this->nl;
        $sXml .= $this->_getXmlIfExists('Color', $product->oxarticles__ahtfarbe->value) . $this->nl;
        $sXml .= '</VariationData>' . $this->nl;
        $sXml .= '</OutdoorLiving>' . $this->nl;
        $sXml .= '</ProductType>' . $this->nl;
        $sXml .= $this->_getXmlIfExists('Parentage', "base-product") . $this->nl;
        $sXml .= '</Home>' . $this->nl;
        $sXml .= '</ProductData>' . $this->nl;
        $sXml .= $this->_getXmlIfExists('RegisteredParameter', "PrivateLabel") . $this->nl;

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
            #$sRet.= $this->_getXmlIfExists('RecommendedBrowseNode' . $iCounter, $aBrowseNode['D3AMAZONCATID']) . $this->nl;
            $sRet.= $this->_getXmlIfExists('RecommendedBrowseNode', $aBrowseNode['D3AMAZONCATID']) . $this->nl;
            $iCounter++;
        }

        if ($sRet == "")
            $sRet.= $this->_getXmlIfExists('RecommendedBrowseNode', "11048231") . $this->nl;

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
            #$sRet.= $this->_getXmlIfExists('SearchTerms' . $iCounter, $aSearchTerm['SearchTerm']) . $this->nl;
            $sRet.= $this->_getXmlIfExists('SearchTerms', $aSearchTerm['SearchTerm']) . $this->nl;
            $iCounter++;
        }
        return $sRet;
    }

    protected function _escapeXmlValue($xml)
    {
        return htmlspecialchars($xml, ENT_NOQUOTES);
        #return htmlspecialchars_decode($xml, ENT_NOQUOTES);
        #return html_entity_decode ($xml);
        #return $xml;
    }

    /**
     * remove 1 from start and end of Sting
     * 
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

    protected function _getXmlIfExistsRaw($tagName, $value, $attributes = array())
    {
        if (isset($value) && strlen($value) > 0)
        {
            $sXml = '<' . $tagName;
            if (count($attributes))
            {
                foreach ($attributes as $attrName => $attrValue)
                {
                    $sXml .= ' ' . $attrName . ' ="' . $this->_escapeXmlAttributeValue($attrValue) . '"';
                }
            }
            $sXml .= '>' . $this->_escapeXmlValue($value) . '</' . $tagName . '>';
            return $sXml;
        }
        return '';
    }

}