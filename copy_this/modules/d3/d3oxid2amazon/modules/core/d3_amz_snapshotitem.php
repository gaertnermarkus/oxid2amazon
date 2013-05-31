<?php

/**
 * Weitere Felder fuer den kompletten Artikelupload - zum Test was geaendert wurde
 * 
 * az_amz_snapshotitem =>d3oxid2amazon/core/d3_amz_snapshotitem
 *  
 */
class d3_amz_snapshotitem extends d3_amz_snapshotitem_parent
{

    protected $_aD3HashProductFields = array(
        'd3amazonbulletpoint1',
        'd3amazonbulletpoint2',
        'd3amazonbulletpoint3',
        'd3amazonbulletpoint4',
        'd3amazonbulletpoint5',
        'AHTFARBE',
        'AHTMATERIAL',
        'AHTSELFIMPORT',
        'OXSEARCHKEYS',
        'AHTPORTALTEXT',
    );
    protected $_aD3HashInventoryFields = array(
        'oxartnum',
    );

    public function getProductHashFields()
    {
        $aFieldsModule = parent::getProductHashFields();
        $aFieldsAH = $this->_aD3HashProductFields;

        return array_merge($aFieldsModule, $aFieldsAH);
    }

    /**
     * @return array 
     */
    public function getInventoryHashFields()
    {
        $aFieldsModule = parent::getInventoryHashFields();
        $aFieldsAH = $this->_aD3HashInventoryFields;
        return array_merge($aFieldsModule, $aFieldsAH);
    }

}