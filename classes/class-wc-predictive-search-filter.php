<?php
/**
 * WooCommerce Predictive Search Hook Filter
 *
 * Hook anf Filter into woocommerce plugin
 *
 * Table Of Contents
 *
 * add_frontend_script()
 * add_query_vars()
 * add_rewrite_rules()
 * custom_rewrite_rule()
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
	
	function add_query_vars($aVars) {
		$aVars[] = "keyword";    // represents the name of the product category as shown in the URL
		$aVars[] = "scat";
		$aVars[] = "stag";
		return $aVars;
	}
	
	function add_rewrite_rules($aRules) {
		//var_dump($_SERVER);
		$woocommerce_search_page_id = get_option('woocommerce_search_page_id');
		$search_page = get_page($woocommerce_search_page_id);
		if (!empty($search_page)) {
			$search_page_slug = $search_page->post_name;
			if (stristr($_SERVER['REQUEST_URI'], $search_page_slug) !== FALSE) {
				//$url_text = stristr($_SERVER['REQUEST_URI'], $search_page_slug);
				$position = strpos($_SERVER['REQUEST_URI'], $search_page_slug);
				$new_url = substr($_SERVER['REQUEST_URI'], ($position + strlen($search_page_slug.'/') ) );
				$parameters_array = explode("/", $new_url);
				
				if (is_array($parameters_array) && count($parameters_array) > 1) {
					$array_key = array();
					$array_value = array();
					$number = 0;
					foreach ($parameters_array as $parameter) {
						$number++;
						if (trim($parameter) == '') continue;
						if ($number%2 == 0) $array_value[] = $parameter;
						else $array_key[] = $parameter;
					}
					if (count($array_key) > 0 && count($array_value) > 0 ) {
						$rewrite_rule = '';
						$original_url = '';
						$number_matches = 0;
						foreach ($array_key as $key) {
							$number_matches++;
							$rewrite_rule .= $key.'/([^/]*)/';
							$original_url .= '&'.$key.'=$matches['.$number_matches.']';
						}
						
						$aNewRules = array($search_page_slug.'/'.$rewrite_rule.'?$' => 'index.php?pagename='.$search_page_slug.$original_url);
						$aRules = $aNewRules + $aRules;
						
					}
				}
			}
		}
		return $aRules;
	} 
	
	function custom_rewrite_rule() {
		// BEGIN rewrite
		// hook add_query_vars function into query_vars
		add_filter('query_vars', array('WC_Predictive_Search_Hook_Filter', 'add_query_vars') );
	
		add_filter('rewrite_rules_array', array('WC_Predictive_Search_Hook_Filter', 'add_rewrite_rules') );
		
		$woocommerce_search_page_id = get_option('woocommerce_search_page_id');
		$search_page = get_page($woocommerce_search_page_id);
		if (!empty($search_page)) {
			$search_page_slug = $search_page->post_name;
			if (stristr($_SERVER['REQUEST_URI'], $search_page_slug) !== FALSE) {
				global $wp_rewrite;
				$wp_rewrite->flush_rules();
			}
		}
		// END rewrite
	}
	
	function search_by_title_only( $search, &$wp_query ) {
		global $wpdb;
		$q = $wp_query->query_vars;
		if ( empty( $search) || !isset($q['s']) )
			return $search; // skip processing - no search term in query
		$search = '';
		$term = esc_sql( like_escape( trim($q['s']) ) );
		$search .= "($wpdb->posts.post_title LIKE '{$term}%' OR $wpdb->posts.post_title LIKE '% {$term}%')";
		if ( ! empty( $search ) ) {
			$search = " AND ({$search}) ";
		}
		return $search;
	}
	
	function predictive_posts_orderby( $orderby, &$wp_query ) {
		global $wpdb;
		$q = $wp_query->query_vars;
		if (isset($q['orderby']) && $q['orderby'] == 'predictive' && isset($q['s']) ) {
			$term = esc_sql( like_escape( trim($q['s']) ) );
			$orderby = "$wpdb->posts.post_title NOT LIKE '{$term}%' ASC, $wpdb->posts.post_title ASC";
		}
		
		return $orderby;
	}
	
	function plugin_extra_links($links, $plugin_name) {
		if ( $plugin_name != WOOPS_NAME) {
			return $links;
		}
		$links[] = '<a href="http://docs.a3rev.com/user-guides/woocommerce/woo-predictive-search/" target="_blank">'.__('Documentation', 'woops').'</a>';
		$links[] = '<a href="'.WOOPS_AUTHOR_URI.'/#help_tab" target="_blank">'.__('Support', 'woops').'</a>';
		return $links;
	}
}
?>