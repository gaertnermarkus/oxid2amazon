<?php

    /**
     *  az_amz_relationshipfeed => d3oxid2amazon/feeds/d3_amz_relationshipfeed
     */
class d3_amz_relationshipfeed extends d3_amz_relationshipfeed_parent
{
    /**
     * @param $id
     *
     * @return string
     */

    public function getUpdateXml($id)
    {
        $amzConfig 	= $this->_getAmzConfig();

        $product 	= $this->_getProduct($id);
        $aVariants 	= $this->_getVariants($id);

        $sSkuProp = $this->getSkuProperty();
        $sSkuValue = $this->prepareEanNumber($product->$sSkuProp->value);


        if ($aVariants && sizeof($aVariants) > 0)
        {
            $sXml = '<Message>'.$this->nl;
            $sXml .= '<MessageID>' . (++$this->_messageId) . '</MessageID>'.$this->nl;
            $sXml .= '<Relationship>'.$this->nl;
            $sXml .= '<ParentSKU>'.$sSkuValue.'</ParentSKU>'.$this->nl;

            foreach($aVariants as $sVariantSKU)
            {
                $sVariantSKU = $this->prepareEanNumber($sVariantSKU);
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
}
