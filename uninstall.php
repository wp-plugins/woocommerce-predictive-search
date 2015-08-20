<?php
/**
 * WC Predictive Search Uninstall
 *
 * Uninstalling deletes options, tables, and pages.
 *
 */
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

global $wpdb;

if ( get_option('woocommerce_search_lite_clean_on_deletion') == 'yes' ) {

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

	delete_option('woocommerce_search_enable_google_analytic');
	delete_option('woocommerce_search_google_analytic_id');
	delete_option('woocommerce_search_google_analytic_query_parameter');

	delete_option('woocommerce_search_remove_special_character');
	delete_option('woocommerce_search_special_characters');

	delete_option('woocommerce_search_lite_clean_on_deletion');

	delete_post_meta_by_key('_predictive_search_focuskw');

	wp_delete_post( get_option('woocommerce_search_page_id') , true );

	$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'ps_posts');
	$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'ps_product_sku');
	$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'ps_exclude');

	$string_ids = $wpdb->get_col("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context='WooCommerce Predictive Search' ");
	if ( is_array( $string_ids ) && count( $string_ids ) > 0 ) {
		$str = join(',', array_map('intval', $string_ids));
		$wpdb->query("
			DELETE s.*, t.* FROM {$wpdb->prefix}icl_strings s LEFT JOIN {$wpdb->prefix}icl_string_translations t ON s.id = t.string_id
			WHERE s.id IN ({$str})");
		$wpdb->query("DELETE FROM {$wpdb->prefix}icl_string_positions WHERE string_id IN ({$str})");
	}
}
