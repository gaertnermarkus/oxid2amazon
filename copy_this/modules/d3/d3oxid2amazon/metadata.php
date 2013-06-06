<?php
/**
 * Metadata version
 */
$sMetadataVersion = '1.0';

/**
 * Module information
 */
$aModule = array(
    'id'          => 'd3oxid2amazon',
    'title'       => 'D&sup3 d3oxid2amazon',
    'description' => array(
        'de' => 'oxid2amazon',
    ),
    'thumbnail'   => 'logo.jpg',
    'version'     => '1.0.0',
    'author'      => 'Anzido / D&sup3',
    'email'       => 'support@shopmodule.com',
    'url'         => 'http://www.oxidmodule.com/',
    'extend'      => array(
        'az_amz_category_theme' => 'd3/d3oxid2amazon/modules/admin/d3_az_amz_category_theme',
        'az_amz_snapshot'       => 'd3/d3oxid2amazon/modules/admin/d3_az_amz_snapshot',
        'az_amz_productfeed'    => 'd3/d3oxid2amazon/modules/amazon/feeds/d3_amz_productfeed',

    ),
    'files'       => array(),
    'blocks'      => array(),
    'settings'    => array()
);