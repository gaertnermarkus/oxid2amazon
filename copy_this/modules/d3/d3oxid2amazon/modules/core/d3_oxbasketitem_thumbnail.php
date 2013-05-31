<?php

/**
 *      oxbasketitem => d3/d3oxid2amazon/modules/core/d3_oxbasketitem_thumbnail
 */

class d3_oxbasketitem_thumbnail extends d3_oxbasketitem_thumbnail_parent
{
    public function __getThumbnailUrl( $bSsl = null )
    {
        $oArticle = $this->getArticle( );
        $sImgName = false;
        $sDirname = "product/1/";
        if ( !$this->_isFieldEmpty( "oxarticles__oxthumb" ) ) {
            $sImgName = basename( $oArticle->oxarticles__oxthumb->value );
            $sDirname = "product/thumb/";
        } elseif ( !$this->_isFieldEmpty( "oxarticles__oxpic1" ) ) {
            $sImgName = basename( $oArticle->oxarticles__oxpic1->value );
        }

        $sSize = $this->getConfig()->getConfigParam( 'sThumbnailsize' );
        return oxPictureHandler::getInstance()->getProductPicUrl( $sDirname, $sImgName, $sSize, 0, $bSsl );
    }
}