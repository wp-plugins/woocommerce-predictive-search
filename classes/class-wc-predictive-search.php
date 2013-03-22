<?php
/**
 * WooCommerce Predictive Search
 *
 * Class Function into woocommerce plugin
 *
 * Table Of Contents
 *
 * plugins_loaded()
 * woops_get_product_thumbnail()
 * woops_limit_words()
 * woops_get_result_popup()
 * create_page()
 * upgrade_version_2_0()
 */
class WC_Predictive_Search{
	
	function plugins_loaded() {
		global $wc_predictive_id_excludes;
		
		WC_Predictive_Search_Settings::get_id_excludes();
	}
	
	function woops_get_product_thumbnail( $post_id, $size = 'shop_catalog', $placeholder_width = 0, $placeholder_height = 0  ) {
		global $woocommerce;
		if ( $placeholder_width == 0 )
			$placeholder_width = $woocommerce->get_image_size( 'shop_catalog_image_width' );
		if ( $placeholder_height == 0 )
			$placeholder_height = $woocommerce->get_image_size( 'shop_catalog_image_height' );
		
		if ( has_post_thumbnail($post_id) ) {
			return get_the_post_thumbnail( $post_id, $size ); 
		}
		
		$mediumSRC = '';
		
		if (trim($mediumSRC == '')) {
			$args = array( 'post_parent' => $post_id ,'numberposts' => 1, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'DESC', 'orderby' => 'ID', 'post_status' => null); 
			$attachments = get_posts($args);
			if ($attachments) {
				foreach ( $attachments as $attachment ) {
					$mediumSRC = wp_get_attachment_image( $attachment->ID, $size, true );
					break;
				}
			}
		}
		
		if (trim($mediumSRC == '')) {
			// Load the product
			$product = get_post( $post_id );
			
			// Get ID of parent product if one exists
			if ( !empty( $product->post_parent ) )
				$post_id = $product->post_parent;
				
			if (has_post_thumbnail($post_id)) {
				return get_the_post_thumbnail( $post_id, $size ); 
			}
			
			if (trim($mediumSRC == '')) {
				$args = array( 'post_parent' => $post_id ,'numberposts' => 1, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'DESC', 'orderby' => 'ID', 'post_status' => null); 
				$attachments = get_posts($args);
				if ($attachments) {
					foreach ( $attachments as $attachment ) {
						$mediumSRC = wp_get_attachment_image( $attachment->ID, $size, true );
						break;
					}
				}
			}
		}
		
		if (trim($mediumSRC != '')) {
			return $mediumSRC;
		} else {
			return '<img src="'. woocommerce_placeholder_img_src() .'" alt="Placeholder" width="' . $placeholder_width . '" height="' . $placeholder_height . '" />';
		}
	}
	
	function woops_limit_words($str='',$len=100,$more) {
		if (trim($len) == '' || $len < 0) $len = 100;
	   if ( $str=="" || $str==NULL ) return $str;
	   if ( is_array($str) ) return $str;
	   $str = trim($str);
	   $str = strip_tags(str_replace("\r\n", "", $str));
	   if ( strlen($str) <= $len ) return $str;
	   $str = substr($str,0,$len);
	   if ( $str != "" ) {
			if ( !substr_count($str," ") ) {
					  if ( $more ) $str .= " ...";
					return $str;
			}
			while( strlen($str) && ($str[strlen($str)-1] != " ") ) {
					$str = substr($str,0,-1);
			}
			$str = substr($str,0,-1);
			if ( $more ) $str .= " ...";
			}
			return $str;
	}
	
	function get_result_popup() {
		check_ajax_referer( 'woops-get-result-popup', 'security' );
		add_filter( 'posts_search', array('WC_Predictive_Search_Hook_Filter', 'search_by_title_only'), 500, 2 );
		add_filter( 'posts_orderby', array('WC_Predictive_Search_Hook_Filter', 'predictive_posts_orderby'), 500, 2 );
		global $wc_predictive_id_excludes;
		$row = 6;
		$text_lenght = 100;
		$search_keyword = '';
		$cat_slug = '';
		$tag_slug = '';
		$extra_parameter = '';
		if (isset($_REQUEST['row']) && $_REQUEST['row'] > 0) $row = stripslashes( strip_tags( $_REQUEST['row'] ) );
		if (isset($_REQUEST['text_lenght']) && $_REQUEST['text_lenght'] >= 0) stripslashes( strip_tags( $text_lenght = $_REQUEST['text_lenght'] ) );
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
				echo "<div class='ajax_search_content_title'>".__('Products', 'woops')."</div>|#|$search_keyword\n";
				foreach ( $search_products as $product ) {
					$link_detail = get_permalink($product->ID);
					$avatar = WC_Predictive_Search::woops_get_product_thumbnail($product->ID,'shop_catalog',64,64);
					$product_description = WC_Predictive_Search::woops_limit_words(strip_tags( strip_shortcodes( str_replace("\n", "", $product->post_content) ) ),$text_lenght,'...');
					if (trim($product_description) == '') $product_description = WC_Predictive_Search::woops_limit_words(strip_tags( strip_shortcodes( str_replace("\n", "", $product->post_excerpt) ) ),$text_lenght,'...');
					$item = '<div class="ajax_search_content"><div class="result_row"><a href="'.$link_detail.'"><span class="rs_avatar">'.$avatar.'</span><div class="rs_content_popup"><span class="rs_name">'.stripslashes( $product->post_title).'</span><span class="rs_description">'.$product_description.'</span></div></a></div></div>';
					echo "$item|$link_detail|".stripslashes( $product->post_title)."\n";
					$end_row--;
					if ($end_row < 1) break;
				}
				$rs_item = '';
				if ( count($search_products) > $row ) {
					if (get_option('permalink_structure') == '')
						$link_search = get_permalink(get_option('woocommerce_search_page_id')).'?rs='.$search_keyword.$extra_parameter;
					else
						$link_search = get_permalink(get_option('woocommerce_search_page_id')).'/keyword/'.$search_keyword.$extra_parameter;
					$rs_item .= '<div class="more_result"><a href="'.$link_search.'">'.__('See more results for', 'woops').' '.$search_keyword.' <span class="see_more_arrow"></span></a><span>'.__('Displaying top', 'woops').' '.$row.' '.__('results', 'woops').'</span></div>';
					echo "$rs_item|$link_search|$search_keyword\n";
				}
			} else {
				echo '<div class="ajax_no_result">'.__('Keep typing...', 'woops').'</div>';
			}
		}
		die();
	}
	
	function create_page( $slug, $option, $page_title = '', $page_content = '', $post_parent = 0 ) {
		global $wpdb;
		 
		$option_value = get_option($option); 
		 
		if ( $option_value > 0 && get_post( $option_value ) ) 
			return;
		
		$page_found = $wpdb->get_var("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = '$slug' LIMIT 1;");
		if ( $page_found ) :
			if ( ! $option_value ) 
				update_option( $option, $page_found );
			return;
		endif;
		
		$page_data = array(
			'post_status' 		=> 'publish',
			'post_type' 		=> 'page',
			'post_author' 		=> 1,
			'post_name' 		=> $slug,
			'post_title' 		=> $page_title,
			'post_content' 		=> $page_content,
			'post_parent' 		=> $post_parent,
			'comment_status' 	=> 'closed'
		);
		$page_id = wp_insert_post( $page_data );
		
		update_option( $option, $page_id );
	}
	
	function upgrade_version_2_0() {
		$exclude_products = get_option('woocommerce_search_exclude_products', '');
		if ($exclude_products !== false) {
			$exclude_products_array = explode(",", $exclude_products);
			if (is_array($exclude_products_array) && count($exclude_products_array) > 0) {
				$exclude_products_array_new = array();
				foreach ($exclude_products_array as $exclude_products_item) {
					if ( trim($exclude_products_item) > 0) $exclude_products_array_new[] = trim($exclude_products_item);
				}
				$exclude_products = $exclude_products_array_new;
			} else {
				$exclude_products = array();
			}
			update_option('woocommerce_search_exclude_products', (array) $exclude_products);
		} else {
			update_option('woocommerce_search_exclude_products', array());
		}
	}
}
?>
