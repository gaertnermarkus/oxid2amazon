<?php

/**
 *  D3 MG 2012-04-19
 * 
 *  az_amz_feed => d3oxid2amazon/core/d3_az_amz_feed
 */
class d3_az_amz_feed extends d3_az_amz_feed_parent
{

    public function getTemporaryExportDir__()
    {
        if (!isset($this->_temporaryDir))
        {
            $sExportDir = oxConfig::getInstance()->getConfigParam('sShopDir');
            #$sExportDir = rtrim($sExportDir, '/\\') . '/' . 'export';
            $sExportDir = rtrim($sExportDir, '/\\') . '/' . 'modules/oxid2amazon/export';
            $this->setTemporaryExportDir($sExportDir);
        }
        return $this->_temporaryDir;
    }

}
