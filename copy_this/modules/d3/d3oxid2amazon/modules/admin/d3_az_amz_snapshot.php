<?php

/**
 * az_amz_snapshot =>d3oxid2amazon/admin/d3_az_amz_snapshot
 */
class d3_az_amz_snapshot extends d3_az_amz_snapshot_parent
{
    /**
     * @return sql string
     */
    protected function _getFilterWhere()
    {
        //TODO: on EE/PE version there could be a problems with table names, couse filter fields comes with oxarticles prefix
        $oDB = oxDb::getDb();

        $sArt2CatView = getViewName('oxobject2category');
        $sArtView     = getViewName('oxarticles');

        $aWhere  = array();
        $aFilter = $this->getFilter();

        $sEanField = $sArtView . '.oxean';

        if($this->_oAZConfig->sEanField) {
            $sEanField = $this->_oAZConfig->sEanField;
        }

        // only fields with non-empty EAN field value	
        // changed by TD, main articles do not need to have an EAN code
        //$aWhere[] = $sEanField." != '' ";
        #$aWhere[] = "($sArtView.$sEanField != '' OR $sArtView.OXVARCOUNT > 0 )";
        $aWhere[] = "($sArtView.oxartnum != '' OR $sArtView.OXVARCOUNT > 0 )";
        $aWhere[] = $sArtView . ".oxparentid = ''";

        if(isset($aFilter['categories'])) {
            $aWhere[] = " $sArt2CatView.oxcatnid IN ('" . implode("','", $aFilter['categories']) . "')";
        }

        if(isset($aFilter['fields']) && sizeof($aFilter['fields']) > 0) {
            foreach ($aFilter['fields'] as $aField) {

                $sWhereLine = $aField['field'] . " " . $aField['operator'];

                if($this->_oAZConfig->isRequiredOperatorValue($aField['operator'])) {

                    $sWhereLine .= " " . $oDB->quote($aField['value']);
                }

                $aWhere[] = $sWhereLine;
            }
        }

        $sWhere = implode(" AND ", $aWhere);

        return $sWhere;
    }

}