<?php
/*
Plugin Name: WooCommerce Predictive Search LITE
Plugin URI: http://a3rev.com/shop/woocommerce-predictive-search/
Description: With WooCommerce Predictive Search Lite you can add an awesome Predictive Products Search widget to any widgetized area on your site.
Version: 2.1.9
Author: A3 Revolution
Author URI: http://www.a3rev.com/
Requires at least: 3.3
Tested up to: 3.6
License: GPLv2 or later

	WooCommerce Predictive Search. Plugin for the WooCommerce plugin.
	Copyright © 2011 A3 Revolution Software Development team

	A3 Revolution Software Development team
	admin@a3rev.com
	PO Box 1170
	Gympie 4570
	QLD Australia
*/
?>
<?php
define( 'WOOPS_FILE_PATH', dirname(__FILE__) );
define( 'WOOPS_DIR_NAME', basename(WOOPS_FILE_PATH) );
define( 'WOOPS_FOLDER', dirname(plugin_basename(__FILE__)) );
define( 'WOOPS_NAME', plugin_basename(__FILE__) );
define( 'WOOPS_URL', WP_CONTENT_URL.'/plugins/'.WOOPS_FOLDER );
define( 'WOOPS_JS_URL',  WOOPS_URL . '/assets/js' );
define( 'WOOPS_CSS_URL',  WOOPS_URL . '/assets/css' );
define( 'WOOPS_IMAGES_URL',  WOOPS_URL . '/assets/images' );
if(!defined("WOOPS_AUTHOR_URI"))
    define("WOOPS_AUTHOR_URI", "http://a3rev.com/shop/woocommerce-predictive-search/");

include 'classes/class-wc-predictive-search-filter.php';
include 'classes/class-wc-predictive-search.php';
include 'classes/class-wc-predictive-search-shortcodes.php';
include 'classes/class-wc-predictive-search-metabox.php';
include 'widget/wc-predictive-search-widgets.php';

include 'admin/classes/class-wc-predictive-search-admin.php';

// Editor
include 'tinymce3/tinymce.php';

include 'admin/wc-predictive-search-init.php';

/**
* Call when the plugin is activated
*/
register_activation_hook(__FILE__,'wc_predictive_install');

function wc_predictive_uninstall() {
	if ( get_option('woocommerce_search_clean_on_deletion') == 1 ) {
		delete_option('woocommerce_search_text_lenght');
		delete_option('woocommerce_search_result_items');
		delete_option('woocommerce_search_sku_enable');
		delete_option('woocommerce_search_price_enable');
		delete_option('woocommerce_search_addtocart_enable');
		delete_option('woocommerce_search_categories_enable');
		delete_option('woocommerce_search_tags_enable');
		delete_option('woocommerce_search_box_text');
		delete_option('woocommerce_search_page_id');
		delete_option('woocommerce_search_exclude_products');
		
		delete_option('woocommerce_search_exclude_p_categories');
		delete_option('woocommerce_search_exclude_p_tags');
		delete_option('woocommerce_search_exclude_posts');
		delete_option('woocommerce_search_exclude_pages');
		delete_option('woocommerce_search_focus_enable');
		delete_option('woocommerce_search_focus_plugin');
		delete_option('woocommerce_search_product_items');
		delete_option('woocommerce_search_p_sku_items');
		delete_option('woocommerce_search_p_cat_items');
		delete_option('woocommerce_search_p_tag_items');
		delete_option('woocommerce_search_post_items');
		delete_option('woocommerce_search_page_items');
		delete_option('woocommerce_search_character_max');
		delete_option('woocommerce_search_width');
		delete_option('woocommerce_search_padding_top');
		delete_option('woocommerce_search_padding_bottom');
		delete_option('woocommerce_search_padding_left');
		delete_option('woocommerce_search_padding_right');
		delete_option('woocommerce_search_custom_style');
		delete_option('woocommerce_search_global_search');
		
		delete_option('woocommerce_search_clean_on_deletion');
		
		delete_post_meta_by_key('_predictive_search_focuskw');
		
		wp_delete_post( get_option('woocommerce_search_page_id') , true );
	}
}
if ( get_option('woocommerce_search_clean_on_deletion') == 1 ) {
	register_uninstall_hook( __FILE__, 'wc_predictive_uninstall' );
}
?>