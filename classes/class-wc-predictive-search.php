<?php
/**
 * WooCommerce Predictive Search
 *
 * Class Function into woocommerce plugin
 *
 * Table Of Contents
 *
 * get_id_excludes()
 * woops_get_product_thumbnail()
 * woops_limit_words()
 * create_page()
 * strip_shortcodes()
 * plugin_extension()
 * predictive_extension_shortcode()
 * upgrade_version_2_0()
 */
class WC_Predictive_Search
{
	public static function get_id_excludes() {
		global $wc_predictive_id_excludes;

		$exclude_products = get_option('woocommerce_search_exclude_products', '');
		if (is_array($exclude_products)) {
			$exclude_products = implode(",", $exclude_products);
		}

		$wc_predictive_id_excludes = array();
		$wc_predictive_id_excludes['exclude_products'] = $exclude_products;

		return $wc_predictive_id_excludes;
	}

	public static function woops_get_product_thumbnail( $post_id, $size = 'shop_catalog', $placeholder_width = 0, $placeholder_height = 0  ) {
		global $woocommerce;
		$woocommerce_db_version = get_option( 'woocommerce_db_version', null );
		$shop_catalog = ( ( version_compare( $woocommerce_db_version, '2.1', '<' ) ) ? $woocommerce->get_image_size( 'shop_catalog' ) : wc_get_image_size( 'shop_catalog' ) );
		if ( is_array( $shop_catalog ) && isset( $shop_catalog['width'] ) && $placeholder_width == 0 ) {
			$placeholder_width = $shop_catalog['width'];
		}
		if ( is_array( $shop_catalog ) && isset( $shop_catalog['height'] ) && $placeholder_height == 0 ) {
			$placeholder_height = $shop_catalog['height'];
		}
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
			return '<img src="'. ( ( version_compare( $woocommerce_db_version, '2.1', '<' ) ) ? woocommerce_placeholder_img_src() : wc_placeholder_img_src() ) .'" alt="Placeholder" width="' . $placeholder_width . '" height="' . $placeholder_height . '" />';
		}
	}

	public static function woops_limit_words($str='',$len=100,$more) {
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

	public static function create_page( $slug, $option, $page_title = '', $page_content = '', $post_parent = 0 ) {
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

	public static function strip_shortcodes ($content='') {
		$content = preg_replace( '|\[(.+?)\](.+?\[/\\1\])?|s', '', $content);

		return $content;
	}

	public static function plugin_extension() {
		$html = '';
		$html .= '<a href="http://a3rev.com/shop/" target="_blank" style="float:right;margin-top:5px; margin-left:10px;" ><div class="a3-plugin-ui-icon a3-plugin-ui-a3-rev-logo"></div></a>';
		$html .= '<h3>'.__('Upgrade to Predictive Search Pro', 'woops').'</h3>';
		$html .= '<p>'.__("<strong>NOTE:</strong> All the functions inside the Yellow border on the plugins admin panel are extra functionality that is activated by upgrading to the Pro version", 'woops').':</p>';
		$html .= '<p>';
		$html .= '<h3>* <a href="'.WOOPS_AUTHOR_URI.'" target="_blank">'.__('WooCommerce Predictive Search Pro', 'woops').'</a> '.__('Features', 'woops').':</h3>';
		$html .= '<p>';
		$html .= '<ul style="padding-left:10px;">';
		$html .= '<li>1. '.__("Activate site search optimization with Predictive Search 'Focus Keywords'.", 'woops').'</li>';
		$html .= '<li>2. '.__('Activate integration with Yoasts WordPress SEO or All in One SEO plugins.', 'woops').'</li>';
		$html .= '<li>3. '.__('Activate the Advance All Search results page customization setting.', 'woops').'</li>';
		$html .= '<li>4. '.__('Activate Search by Product Categories, Product Tags, Posts and Pages options in the search widgets.', 'woops').'</li>';
		$html .= '<li>5. '.__('Activate Search shortcodes for Posts and pages.', 'woops').'</li>';
		$html .= '<li>6. '.__('Activate Exclude Product Cats, Product Tags , Posts and pages from search results.', 'woops').'</li>';
		$html .= '<li>7. '.__('Activate Predictive Search Function to place the search box in any non widget area of your site - example the header.', 'woops').'</li>';
		$html .= '<li>8. '.__("Activate 'Smart Search' function on Widgets, Shortcode and the search Function", 'woops').'</li>';
		$html .= '<li>9. '.__("Multi Lingual Support. Fully compatible with WPML", 'woops').'</li>';
		$html .= '</ul>';
		$html .= '</p>';
		$html .= '<h3>'.__('View this plugins', 'woops').' <a href="http://docs.a3rev.com/user-guides/woocommerce/woo-predictive-search/" target="_blank">'.__('documentation', 'woops').'</a></h3>';
		$html .= '<h3>'.__('Visit this plugins', 'woops').' <a href="http://wordpress.org/support/plugin/woocommerce-predictive-search/" target="_blank">'.__('support forum', 'woops').'</a></h3>';
		$html .= '<h3>'.__('More a3rev Quality Plugins', 'woops').'</h3>';
		$html .= '<p>'.__('Below is a list of the a3rev plugins that are available for free download from wordpress.org', 'woops').'</p>';
		$html .= '<h3>'.__('More FREE a3rev WooCommerce Plugins', 'woops').'</h3>';
		$html .= '<p>';
		$html .= '<ul style="padding-left:10px;">';
		$html .= '<li>* <a href="http://wordpress.org/plugins/woocommerce-product-sort-and-display/" target="_blank">'.__('WooCommerce Product Sort & Display', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/plugins/woocommerce-products-quick-view/" target="_blank">'.__('WooCommerce Products Quick View', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/plugins/woocommerce-dynamic-gallery/" target="_blank">'.__('WooCommerce Dynamic Products Gallery', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/plugins/woocommerce-compare-products/" target="_blank">'.__('WooCommerce Compare Products', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/plugins/woo-widget-product-slideshow/" target="_blank">'.__('WooCommerce Widget Product Slideshow', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/plugins/woocommerce-email-inquiry-cart-options/" target="_blank">'.__('WooCommerce Email Inquiry & Cart Options', 'woops').'</a></li>';
		$html .= '</ul>';
		$html .= '</p>';
		$html .= '<h3>'.__('FREE a3rev WordPress Plugins', 'woops').'</h3>';
		$html .= '<p>';
		$html .= '<ul style="padding-left:10px;">';
		$html .= '<li>* <a href="https://wordpress.org/plugins/a3-lazy-load/" target="_blank">'.__('a3 Lazy Load', 'woops').'</a> ('.__( 'WooCommerce Compatible' , 'woops' ).')</li>';
		$html .= '<li>* <a href="https://wordpress.org/plugins/a3-portfolio/" target="_blank">'.__('a3 Portfolio', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/plugins/a3-responsive-slider/" target="_blank">'.__('a3 Responsive Slider', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/plugins/contact-us-page-contact-people/" target="_blank">'.__('Contact Us Page - Contact People', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/plugins/wp-email-template/" target="_blank">'.__('WordPress Email Template', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/plugins/page-views-count/" target="_blank">'.__('Page View Count', 'woops').'</a></li>';
		$html .= '</ul>';
		$html .= '</p>';
		return $html;
	}

	public static function predictive_extension_shortcode() {
		$html = '';
		$html .= '<div id="woo_predictive_extensions">'.__("Yes you'll love the Predictive Search shortcode feature. Upgrading to the", 'woops').' <a target="_blank" href="'.WOOPS_AUTHOR_URI.'">'.__('Pro Version', 'woops').'</a> '.__("activates this shortcode feature as well as the awesome 'Smart Search' feature, per widget controls, the All Search Results page customization settings and function features.", 'woops').'</div>';
		return $html;
	}

	public static function upgrade_version_2_0() {
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
