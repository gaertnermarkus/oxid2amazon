<?php
/**
 * Metadata version
 */
$sMetadataVersion = '1.0';

/**
 * Module information
 */
$aModule = array(
    'id'           => 'd3oxid2amazon',
    'title'        => 'D&sup3 d3oxid2amazon',
    'description'  => array(
        'de' => 'oxid2amazon',
    ),
    'thumbnail'    => 'logo.jpg',
    'version'      => '1.0.0',
    'author'       => 'Anzido / D&sup3',
    'email'        => 'support@shopmodule.com',
    'url'          => 'http://www.oxidmodule.com/',
    'extend'       => array(
        'az_amz_category_theme' => 'd3/d3oxid2amazon/modules/admin/d3_az_amz_category_theme',
        'az_amz_snapshot' => 'd3/d3oxid2amazon/modules/admin/d3_az_amz_snapshot',

        'az_amz_feed' 			=> 'd3/d3oxid2amazon/modules/core/d3_amz_feed',
        'az_amz_orders' 		=> 'd3/d3oxid2amazon/modules/core/d3_amz_orders',
        'az_amz_snapshotitem'   => 'd3/d3oxid2amazon/modules/core/d3_amz_snapshotitem',
        'az_amz_feed' 			=> 'd3/d3oxid2amazon/modules/core/d3_az_amz_feed',
        'az_amz_productfeed'    => 'd3/d3oxid2amazon/modules/core/d3_az_amz_productfeed',
        'oxarticle' 			=> 'd3/d3oxid2amazon/modules/core/d3_oxarticle_amazoncategoies',
        'oxbasket' 				=> 'd3/d3oxid2amazon/modules/core/d3_oxbasket_amazon',
        'oxemail' 				=> 'd3/d3oxid2amazon/modules/core/d3_oxemail_amazon',
        'oxorder' 				=> 'd3/d3oxid2amazon/modules/core/d3_oxorder_save',
        'oxorderarticle' 		=> 'd3/d3oxid2amazon/modules/core/d3_oxorderarticle_save',
        #'oxbasketitem'          => 'd3/d3oxid2amazon/modules/core/d3_oxbasketitem_thumbnail',

        'az_amz_inventoryfeed' 			=> 'd3/d3oxid2amazon/modules/amazon/feeds/d3_amz_inventoryfeed',
        'az_amz_pricefeed' 			    => 'd3/d3oxid2amazon/modules/amazon/feeds/d3_amz_pricefeed',
        'az_amz_productfeed' 			=> 'd3/d3oxid2amazon/modules/amazon/feeds/d3_amz_productfeed',
        'az_amz_productimagesfeed' 		=> 'd3/d3oxid2amazon/modules/amazon/feeds/d3_amz_productimagesfeed',
        'az_amz_relationshipfeed' 		=> 'd3/d3oxid2amazon/modules/amazon/feeds/d3_amz_relationshipfeed',

        'az_amz_cron' 						=> 'd3/d3oxid2amazon/modules/views/d3_amz_cron',
	
    ),
    'files' => array(   
        'az_amz_categories'                 => 'd3/d3oxid2amazon/core/az_amz_categories.php',	
    ),
    'blocks' => array(
        array('template' => 'email/html/order_owner.tpl',        'block' => 'email_html_order_owner_orderemail',                      'file' => 'out/blocks/oxadminorderemail_amazon_html.tpl'),
        array('template' => 'email/plain/order_owner.tpl',        'block' => 'email_plain_order_owner_orderemail',                      'file' => 'out/blocks/oxadminorderemail_amazon_plain.tpl'),
    ),
   'settings' => array(
    )
);