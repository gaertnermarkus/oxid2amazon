<?php
/**
 * Metadata version
 */
$sMetadataVersion = '1.0';

/**
 * Module information
 */
$aModule = array(
    'id'          => 'oxid2amazon',
    'title'       => 'oxid2amazon',
    'description' => array(
        'de' => 'oxid2amazon".',
    ),
    'thumbnail'   => 'logo.jpg',
    'version'     => '1.0.0',
    'author'      => 'Anzido / d3',
    'url'         => '',
    'email'       => '',
    'extend'      => array( #'order'        => 'anzido/oxid2amazon/views/oepaypalorder',
        'oxemail'             => 'anzido/modules/core/az_oxemail_amazon',
        'oxorder'             => 'anzido/modules/core/d3_oxorder_save',
        'oxbasket'            => 'anzido/modules/core/d3_oxbasket_amazon',
        'oxarticle'           => 'anzido/modules/core/d3_oxarticle_amazoncategoies',
        'az_amz_snapshotitem' => 'anzido/modules/core/d3_amz_snapshotitem',
    ),
    'files'       => array(
        'az_amz_config'                    => 'anzido/oxid2amazon/core/az_amz_config.php',
        'az_amz_destination'               => 'anzido/oxid2amazon/core/az_amz_destination.php',
        'az_amz_feed'                      => 'anzido/oxid2amazon/core/az_amz_feed.php',
        'az_amz_ftp'                       => 'anzido/oxid2amazon/core/az_amz_ftp.php',
        'az_amz_history'                   => 'anzido/oxid2amazon/core/az_amz_history.php',
        'az_amz_orders'                    => 'anzido/oxid2amazon/core/az_amz_orders.php',
        'az_amz_snapshot'                  => 'anzido/oxid2amazon/core/az_amz_snapshot.php',
        'az_amz_snapshotitem'              => 'anzido/oxid2amazon/core/az_amz_snapshotitem.php',
        'az_amz_theme'                     => 'anzido/oxid2amazon/core/az_amz_theme.php',
        'az_amz_categories'                => 'anzido/oxid2amazon/core/az_amz_categories.php',
        'az_amz_category_theme'            => 'anzido/oxid2amazon/admin/az_amz_category_theme.php',
        'az_amz_destinations'              => 'anzido/oxid2amazon/admin/az_amz_destinations.php',
        'az_amz_destinations_history'      => 'anzido/oxid2amazon/admin/az_amz_destinations_history.php',
        'az_amz_destinations_list'         => 'anzido/oxid2amazon/admin/az_amz_destinations_list.php',
        'az_amz_destinations_main'         => 'anzido/oxid2amazon/admin/az_amz_destinations_main.php',
        'az_amz_destinations_prodselector' => 'anzido/oxid2amazon/admin/az_amz_destinations_prodselector.php',
        'az_amz_settings'                  => 'anzido/oxid2amazon/admin/az_amz_settings.php',
        'az_amz_settings_categories'       => 'anzido/oxid2amazon/admin/az_amz_settings_categories.php',
        'az_amz_settings_list'             => 'anzido/oxid2amazon/admin/az_amz_settings_list.php',
        'az_amz_settings_main'             => 'anzido/oxid2amazon/admin/az_amz_settings_main.php',
        'az_amz_cron'                      => 'anzido/oxid2amazon/views/az_amz_cron.php',
        'amz_autoaccessory_theme'          => 'anzido/oxid2amazon/amazon/themes/amz_autoaccessory_theme.php',
        'amz_beauty_theme'                 => 'anzido/oxid2amazon/amazon/themes/amz_beauty_theme.php',
        'amz_cameraphoto_theme'            => 'anzido/oxid2amazon/amazon/themes/amz_cameraphoto_theme.php',
        'amz_ce_theme'                     => 'anzido/oxid2amazon/amazon/themes/amz_ce_theme.php',
        'amz_clothing_theme'               => 'anzido/oxid2amazon/amazon/themes/amz_clothing_theme.php',
        'amz_foodandbeverages_theme'       => 'anzido/oxid2amazon/amazon/themes/amz_foodandbeverages_theme.php',
        'amz_gourmet_theme'                => 'anzido/oxid2amazon/amazon/themes/amz_gourmet_theme.php',
        'amz_health_theme'                 => 'anzido/oxid2amazon/amazon/themes/amz_home_theme.php',
        'amz_home_theme'                   => 'anzido/oxid2amazon/amazon/themes/az_amz_cron.php',
        'amz_jewelry_theme'                => 'anzido/oxid2amazon/amazon/themes/amz_jewelry_theme.php',
        'amz_miscellaneous_theme'          => 'anzido/oxid2amazon/amazon/themes/amz_miscellaneous_theme.php',
        'amz_musicalinstruments_theme'     => 'anzido/oxid2amazon/amazon/themes/amz_musicalinstruments_theme.php',
        'amz_office_theme'                 => 'anzido/oxid2amazon/amazon/themes/amz_office_theme.php',
        'amz_petsupplies_theme'            => 'anzido/oxid2amazon/amazon/themes/amz_petsupplies_theme.php',
        'amz_sports_theme'                 => 'anzido/oxid2amazon/amazon/themes/amz_sports_theme.php',
        'amz_swvg_theme'                   => 'anzido/oxid2amazon/amazon/themes/amz_swvg_theme.php',
        'amz_tiresandwheels_theme'         => 'anzido/oxid2amazon/amazon/themes/amz_tiresandwheels_theme.php',
        'amz_tools_theme'                  => 'anzido/oxid2amazon/amazon/themes/amz_tools_theme.php',
        'amz_toysbaby_theme'               => 'anzido/oxid2amazon/amazon/themes/amz_toysbaby_theme.php',
        'amz_wireless_theme'               => 'anzido/oxid2amazon/amazon/themes/amz_wireless_theme.php',
        'az_amz_inventoryfeed'             => 'anzido/oxid2amazon/amazon/feeds/az_amz_inventoryfeed.php',
        'az_amz_pricefeed'                 => 'anzido/oxid2amazon/amazon/feeds/az_amz_pricefeed.php',
        'az_amz_productfeed'               => 'anzido/oxid2amazon/amazon/feeds/az_amz_productfeed.php',
        'az_amz_productimagesfeed'         => 'anzido/oxid2amazon/amazon/feeds/az_amz_productimagesfeed.php',
        'az_amz_relationshipfeed'          => 'anzido/oxid2amazon/amazon/feeds/az_amz_relationshipfeed.php',
        'az_amz_removeallfeed'             => 'anzido/oxid2amazon/amazon/feeds/az_amz_removeallfeed.php',
        'az_amz_shippingfeed'              => 'anzido/oxid2amazon/amazon/feeds/az_amz_shippingfeed.php',
    ),
    'blocks'      => array(
        array(
            'template' => 'email/html/order_owner.tpl',
            'block'    => 'email_html_order_owner_orderemail',
            'file'     => 'out/blocks/oxadminorderemail_amazon_html.tpl'
        ),
        array(
            'template' => 'email/plain/order_owner.tpl',
            'block'    => 'email_plain_order_owner_orderemail',
            'file'     => 'out/blocks/oxadminorderemail_amazon_plain.tpl'
        ),
    ),
    'settings'    => array()
);