<?php

/**
 * D3 /MG 2012-04-26
 * 
 * genexport_do =>d3oxid2amazon/admin/d3_genexportdo_zeichen
 *  
 */
class d3_genexportdo_zeichen extends d3_genexportdo_zeichen_parent
{

    public function write($sLine)
    {
        $sLine = $this->removeSID($sLine);

        $sLine = str_replace(array("\r\n", "\n"), "", $sLine);
        #$sLine = str_replace("<br>", "\n", $sLine);

        fwrite($this->fpFile, $sLine . "\r\n");
    }

}