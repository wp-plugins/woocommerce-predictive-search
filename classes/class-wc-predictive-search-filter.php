<?php
/**
 * WooCommerce Predictive Search Hook Filter
 *
 * Hook anf Filter into woocommerce plugin
 *
 * Table Of Contents
 *
 * plugins_loaded()
 * get_result_popup()
 * add_frontend_script()
 * add_frontend_style()
 * add_query_vars()
 * add_rewrite_rules()
 * custom_rewrite_rule()
 * search_by_title_only()
 * posts_request_unconflict_role_scoper_plugin()
 * admin_add_scripts()
 * plugin_extra_links()
 */
class WC_Predictive_Search_Hook_Filter
{
	public static function plugins_loaded() {
		global $wc_predictive_id_excludes;
		
		WC_Predictive_Search::get_id_excludes();
	}
	
	public static function get_result_popup() {
		check_ajax_referer( 'woops-get-result-popup', 'security' );
		add_filter( 'posts_search', array('WC_Predictive_Search_Hook_Filter', 'search_by_title_only'), 500, 2 );
		add_filter( 'posts_orderby', array('WC_Predictive_Search_Hook_Filter', 'predictive_posts_orderby'), 500, 2 );
		add_filter( 'posts_request', array('WC_Predictive_Search_Hook_Filter', 'posts_request_unconflict_role_scoper_plugin'), 500, 2);
		global $wc_predictive_id_excludes;
		$row = 6;
		$text_lenght = 100;
		$show_price = 1;
		$search_keyword = '';
		$cat_slug = '';
		$tag_slug = '';
		$extra_parameter = '';
		if (isset($_REQUEST['row']) && $_REQUEST['row'] > 0) $row = stripslashes( strip_tags( $_REQUEST['row'] ) );
		if (isset($_REQUEST['text_lenght']) && $_REQUEST['text_lenght'] >= 0) stripslashes( strip_tags( $text_lenght = $_REQUEST['text_lenght'] ) );
		if (isset($_REQUEST['show_price']) && trim($_REQUEST['show_price']) != '') $show_price = stripslashes( strip_tags( $_REQUEST['show_price'] ) );
		if (isset($_REQUEST['q']) && trim($_REQUEST['q']) != '') $search_keyword = stripslashes( strip_tags( $_REQUEST['q'] ) );
		
		$end_row = $row;
		
		if ($search_keyword != '') {
			$args = array( 's' => $search_keyword, 'numberposts' => $row+1, 'offset'=> 0, 'orderby' => 'predictive', 'order' => 'ASC', 'post_type' => 'product', 'post_status' => 'publish', 'exclude' => $wc_predictive_id_excludes['exclude_products'], 'suppress_filters' => FALSE);
			if ($cat_slug != '') {
				$args['tax_query'] = array( array('taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => $cat_slug) );
				if (get_option('permalink_structure') == '')
					$extra_parameter .= '&scat='.$cat_slug;
				else
					$extra_parameter .= '/scat/'.$cat_slug;
			} elseif($tag_slug != '') {
				$args['tax_query'] = array( array('taxonomy' => 'product_tag', 'field' => 'slug', 'terms' => $tag_slug) );
				if (get_option('permalink_structure') == '')
					$extra_parameter .= '&stag='.$tag_slug;
				else
					$extra_parameter .= '/stag/'.$tag_slug;
			}
			$total_args = $args;
			$total_args['numberposts'] = -1;
			
			//$search_all_products = get_posts($total_args);
			
			$search_products = get_posts($args);
						
			if ( $search_products && count($search_products) > 0 ) {
				echo "<div class='ajax_search_content_title'>".__('Products', 'woops')."</div>[|]#[|]$search_keyword\n";
				foreach ( $search_products as $product ) {
					$link_detail = get_permalink($product->ID);
					$avatar = WC_Predictive_Search::woops_get_product_thumbnail($product->ID,'shop_catalog',64,64);
					$product_description = WC_Predictive_Search::woops_limit_words(strip_tags( WC_Predictive_Search::strip_shortcodes( strip_shortcodes( str_replace("\n", "", $product->post_content) ) ) ),$text_lenght,'...');
					if (trim($product_description) == '') $product_description = WC_Predictive_Search::woops_limit_words(strip_tags( WC_Predictive_Search::strip_shortcodes( strip_shortcodes( str_replace("\n", "", $product->post_excerpt) ) ) ),$text_lenght,'...');
					
					$price_html = '';
					if ( $show_price == 1)
						$price_html = WC_Predictive_Search_Shortcodes::get_product_price_dropdown($product->ID);
							
					$item = '<div class="ajax_search_content"><div class="result_row"><a href="'.$link_detail.'"><span class="rs_avatar">'.$avatar.'</span><div class="rs_content_popup"><span class="rs_name">'.stripslashes( $product->post_title).'</span>'.$price_html.'<span class="rs_description">'.$product_description.'</span></div></a></div></div>';
					echo $item.'[|]'.$link_detail.'[|]'.stripslashes( $product->post_title)."\n";
					$end_row--;
					if ($end_row < 1) break;
				}
				$rs_item = '';
				if ( count($search_products) > $row ) {
					if (get_option('permalink_structure') == '')
						$link_search = get_permalink(get_option('woocommerce_search_page_id')).'&rs='.$search_keyword.$extra_parameter;
					else
						$link_search = rtrim( get_permalink(get_option('woocommerce_search_page_id')), '/' ).'/keyword/'.$search_keyword.$extra_parameter;
					$rs_item .= '<div class="more_result" rel="more_result"><a href="'.$link_search.'">'.__('See more results for', 'woops').' '.$search_keyword.' <span class="see_more_arrow"></span></a><span>'.__('Displaying top', 'woops').' '.$row.' '.__('results', 'woops').'</span></div>';
					echo $rs_item.'[|]'.$link_search.'[|]'.$search_keyword."\n";
				}
			} else {
				echo '<div class="ajax_no_result">'.__('Keep typing...', 'woops').'</div>';
			}
		}
		die();
	}
	
	/*
	* Include the script for widget search and Search page
	*/
	public static function add_frontend_script() {
		wp_enqueue_script('jquery');
		wp_enqueue_script( 'ajax-woo-autocomplete-script', WOOPS_JS_URL . '/ajax-autocomplete/jquery.autocomplete.js', array(), false, true );
	}
	
	public static function add_frontend_style() {
		wp_enqueue_style( 'ajax-woo-autocomplete-style', WOOPS_JS_URL . '/ajax-autocomplete/jquery.autocomplete.css' );
	}
	
	public static function add_query_vars($aVars) {
		$aVars[] = "keyword";    // represents the name of the product category as shown in the URL
		$aVars[] = "scat";
		$aVars[] = "stag";
		return $aVars;
	}
	
	public static function add_rewrite_rules($aRules) {
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
	
	public static function custom_rewrite_rule() {
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
	
	public static function search_by_title_only( $search, &$wp_query ) {
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
	
	public static function predictive_posts_orderby( $orderby, &$wp_query ) {
		global $wpdb;
		$q = $wp_query->query_vars;
		if (isset($q['orderby']) && $q['orderby'] == 'predictive' && isset($q['s']) ) {
			$term = esc_sql( like_escape( trim($q['s']) ) );
			$orderby = "$wpdb->posts.post_title NOT LIKE '{$term}%' ASC, $wpdb->posts.post_title ASC";
		}
		
		return $orderby;
	}
	
	public static function posts_request_unconflict_role_scoper_plugin( $posts_request, &$wp_query ) {
		$posts_request = str_replace('1=2', '2=2', $posts_request);
		
		return $posts_request;
	}
	
	public static function admin_add_scripts(){
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script('jquery');
		
		wp_enqueue_style( 'a3rev-chosen-style', WOOPS_JS_URL . '/chosen/chosen.css' );
		wp_enqueue_script( 'chosen', WOOPS_JS_URL . '/chosen/chosen.jquery'.$suffix.'.js', array(), false, true );
		
		wp_enqueue_script( 'a3rev-chosen-script-init', WOOPS_JS_URL.'/init-chosen.js', array(), false, true );
	}
	
	public static function plugin_extra_links($links, $plugin_name) {
		if ( $plugin_name != WOOPS_NAME) {
			return $links;
		}
		$links[] = '<a href="'.WOO_PREDICTIVE_SEARCH_DOCS_URI.'" target="_blank">'.__('Documentation', 'woops').'</a>';
		$links[] = '<a href="http://wordpress.org/support/plugin/woocommerce-predictive-search/" target="_blank">'.__('Support', 'woops').'</a>';
		return $links;
	}
}
?>