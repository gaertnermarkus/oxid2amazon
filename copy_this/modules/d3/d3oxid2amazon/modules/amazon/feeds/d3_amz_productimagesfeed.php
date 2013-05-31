<?php

/**
 * D3 MG 2012-05-18     
 *
 *  az_amz_productimagesFeed => d3oxid2amazon/feeds/d3_amz_productimagesfeed
 */
class d3_amz_productimagesfeed extends d3_amz_productimagesfeed_parent
{

    public function getUpdateXml($id)
    {
        $oAmzConfig = $this->_getAmzConfig();

        /* @var $product oxarticle */
        #$product = oxNew('oxarticle');
        #$product->load($id);

        $product = $this->_getProduct($id);

        for ($i = 1; $i < 9; $i++)
        {
            $sConfigFieldName = 'sPicField' . $i;
            if (isset($oAmzConfig->$sConfigFieldName))
            {
                $sPicField = "oxarticles__" . $oAmzConfig->$sConfigFieldName;

                if (isset($product->$sPicField->value))
                {
                    $sImageType = ($i == 1 ? 'Main' : 'PT' . ($i - 1));

                    if (stripos($sPicField, 'oxpic'))
                    {
                        $iImgIndex = (int) str_replace('oxarticles__oxpic', '', $sPicField);
                        $sImageLocation = $product->getPictureUrl($iImgIndex);
                    }
                    if(preg_match('/nopic.jpg$/', $sImageLocation))
                         $sImageLocation = '';   

                    if ($sImageLocation)
                    {
                        //https entfernen,                     
                        $sImageLocation = str_replace('https', 'http', $sImageLocation);

                        $sSkuProp = $this->getSkuProperty();
                        $sSkuValue = $this->cutEanNumber($product->$sSkuProp->value);


                        $sXml .= '<Message>' . $this->nl;
                        $sXml .= '<MessageID>' . (++$this->_messageId) . '</MessageID>' . $this->nl;
                        $sXml .= '<OperationType>Update</OperationType>' . $this->nl;
                        $sXml .= '<ProductImage>' . $this->nl;
                        $sXml .= '<SKU>' . $sSkuValue . '</SKU>' . $this->nl;
                        $sXml .= '<ImageType>' . $sImageType . '</ImageType>' . $this->nl;
                        $sXml .= '<ImageLocation>' . $sImageLocation . '</ImageLocation>' . $this->nl;
                        $sXml .= '</ProductImage>' . $this->nl;
                        $sXml .= '</Message>' . $this->nl;
                    }
                }
            }
        }
        return $sXml;
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

}