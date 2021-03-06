<?php

/**
 * Weitere Felder fuer den kompletten Artikelupload
 * az_amz_snapshotitem =>d3oxid2amazon/core/d3_amz_snapshotitem

 */
class d3_amz_snapshotitem extends d3_amz_snapshotitem_parent
{

    /**
     * more fields in oxarticle
     *
     * @var array
     */
    protected $_aD3HashProductFields = array(
        'd3amazonbulletpoint1',
        'd3amazonbulletpoint2',
        'd3amazonbulletpoint3',
        'd3amazonbulletpoint4',
        'd3amazonbulletpoint5',
        'FARBE',
        'MATERIAL',
        'SELFIMPORT',
        'OXSEARCHKEYS',
        'PORTALTEXT',
    );

    protected $_aD3HashInventoryFields = array(
        'oxartnum',
    );

    /**
     * @return array
     */
    public function getProductHashFields()
    {
        $aFieldsModule = parent::getProductHashFields();
        $aFieldsAH     = $this->_aD3HashProductFields;

        return array_merge($aFieldsModule, $aFieldsAH);
    }

    /**
     * @return array
     */
    public function getInventoryHashFields()
    {
        $aFieldsModule = parent::getInventoryHashFields();
        $aFieldsAH     = $this->_aD3HashInventoryFields;
        return array_merge($aFieldsModule, $aFieldsAH);
    }

}