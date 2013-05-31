<?php

/**
 *  D3 MG 2012-04-19
 * 
 *  az_amz_feed => d3oxid2amazon/core/d3_amz_feed
 */
class d3_amz_feed extends d3_amz_feed_parent
{

    public function cutEanNumber__($sArtikelNr)
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
