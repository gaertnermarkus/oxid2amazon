<?php

    /**
     *  az_amz_relationshipfeed => d3oxid2amazon/feeds/d3_amz_relationshipfeed
     */
class d3_amz_relationshipfeed extends d3_amz_relationshipfeed_parent
{

    public function getUpdateXml($id)
    {
        $amzConfig 	= $this->_getAmzConfig();

        $product 	= $this->_getProduct($id);
        $aVariants 	= $this->_getVariants($id);

        $sSkuProp = $this->getSkuProperty();
        $sSkuValue = $this->cutEanNumber($product->$sSkuProp->value);


        if ($aVariants && sizeof($aVariants) > 0)
        {
            $sXml = '<Message>'.$this->nl;
            $sXml .= '<MessageID>' . (++$this->_messageId) . '</MessageID>'.$this->nl;
            $sXml .= '<Relationship>'.$this->nl;
            $sXml .= '<ParentSKU>'.$sSkuValue.'</ParentSKU>'.$this->nl;

            foreach($aVariants as $sVariantSKU)
            {
                $sVariantSKU = $this->cutEanNumber($sVariantSKU);
                $sXml .= '<Relation>'.$this->nl;
                $sXml .= '<SKU>'.$sVariantSKU.'</SKU>'.$this->nl;
                $sXml .= '<Type>Variation</Type>'.$this->nl;
                $sXml .= '</Relation>'.$this->nl;
            }
            $sXml .= '</Relationship>'.$this->nl;
            $sXml .= '</Message>'.$this->nl;
        }

        return $sXml;
    }

    /**
     * remove 1 from start and end of Sting
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
