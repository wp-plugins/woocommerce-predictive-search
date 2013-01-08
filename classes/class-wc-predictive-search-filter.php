<?php
/**
 * WooCommerce Predictive Search Hook Filter
 *
 * Hook anf Filter into woocommerce plugin
 *
 * Table Of Contents
 *
 * add_frontend_script()
 * search_by_title_only()
 * plugin_extra_links()
 */
class WC_Predictive_Search_Hook_Filter {
	
	/*
	* Include the script for widget search and Search page
	*/
	function add_frontend_script() {
		wp_enqueue_style( 'ajax-woo-autocomplete-style', WOOPS_JS_URL . '/ajax-autocomplete/jquery.autocomplete.css' );
		wp_enqueue_script( 'ajax-woo-autocomplete-script', WOOPS_JS_URL . '/ajax-autocomplete/jquery.autocomplete.js', array(), false, true );
	}
	
	function search_by_title_only( $search, &$wp_query ) {
		global $wpdb;
		$q = $wp_query->query_vars;
		if ( empty( $search) )
			return $search; // skip processing - no search term in query
		$search = '';
		$term = esc_sql( like_escape( trim($q['s']) ) );
		$search .= "($wpdb->posts.post_title LIKE '{$term}%' OR $wpdb->posts.post_title LIKE '% {$term}%')";
		if ( ! empty( $search ) ) {
			$search = " AND ({$search}) ";
		}
		return $search;
	}
	
	function plugin_extra_links($links, $plugin_name) {
		if ( $plugin_name != WOOPS_NAME) {
			return $links;
		}
		$links[] = '<a href="http://docs.a3rev.com/user-guides/woocommerce/woo-predictive-search/" target="_blank">'.__('Documentation', 'woops').'</a>';
		$links[] = '<a href="'.WOOPS_AUTHOR_URI.'/#tab-reviews" target="_blank">'.__('Support', 'woops').'</a>';
		return $links;
	}
}
?>