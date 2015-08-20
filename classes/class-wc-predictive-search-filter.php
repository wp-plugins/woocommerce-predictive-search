<?php
/**
 * WooCommerce Predictive Search Hook Filter
 *
 * Hook anf Filter into woocommerce plugin
 *
 * Table Of Contents
 *
 * plugins_loaded()
 * add_frontend_style()
 * a3_wp_admin()
 * yellow_message_dontshow()
 * yellow_message_dismiss()
 * plugin_extra_links()
 */
class WC_Predictive_Search_Hook_Filter
{

	public static function plugins_loaded() {
		global $woocommerce_search_page_id;

		$woocommerce_search_page_id = WC_Predictive_Search_Functions::get_page_id_from_shortcode( 'woocommerce_search', 'woocommerce_search_page_id');
	}

	public static function add_frontend_style() {
		wp_enqueue_style( 'ajax-woo-autocomplete-style', WOOPS_JS_URL . '/ajax-autocomplete/jquery.autocomplete.css' );
	}

	public static function a3_wp_admin() {
		wp_enqueue_style( 'a3rev-wp-admin-style', WOOPS_CSS_URL . '/a3_wp_admin.css' );
	}

	public static function yellow_message_dontshow() {
		check_ajax_referer( 'wc_ps_yellow_message_dontshow', 'security' );
		$option_name   = $_REQUEST['option_name'];
		update_option( $option_name, 1 );
		die();
	}

	public static function yellow_message_dismiss() {
		check_ajax_referer( 'wc_ps_yellow_message_dismiss', 'security' );
		$session_name   = $_REQUEST['session_name'];
		if ( !isset($_SESSION) ) { @session_start(); }
		$_SESSION[$session_name] = 1 ;
		die();
	}

	public static function plugin_extension() {
		$html = '';
		$html .= '<a href="http://a3rev.com/shop/" target="_blank" style="float:right;margin-top:5px; margin-left:10px;" ><div class="a3-plugin-ui-icon a3-plugin-ui-a3-rev-logo"></div></a>';
		$html .= '<h3>'.__('Predictive Search Premium', 'woops').'</h3>';
		$html .= '<p>'.__("<strong>WANT MORE?</strong> - WooCommerce Predictive Search Premium gives a much deeper store catalog search", 'woops').':</p>';
		$html .= '<p>';
		$html .= '<h3>* <a href="'.WOOPS_AUTHOR_URI.'" target="_blank">'.__('WooCommerce Predictive Search Premium', 'woops').'</a> '.__('Features', 'woops').':</h3>';
		$html .= '<p>';
		$html .= '<ul style="padding-left:10px; list-style: inside none disc;">';
		$html .= '<li>'.__("Search options includes Product Categories, Product SKU's, Product Tags.", 'woops').'</li>';
		$html .= '<li>'.__("Full Site search optimization with Predictive Search 'Focus Keywords'.", 'woops').'</li>';
		$html .= '<li>'.__("Full integration with Yoast SEO or All in One SEO plugins.", 'woops').'</li>';
		$html .= '<li>'.__("Insert Predictive Search boxes by shortcode.", 'woops').'</li>';
		$html .= '<li>'.__("Exclude Product Cats, Product Tags from search results.", 'woops').'</li>';
		$html .= '<li>'.__("Predictive Search PHP Function for adding the search box in any non widget area - example the header.", 'woops').'</li>';
		$html .= '<li>'.__("Smart Search function with Widgets, Shortcode and the search Function.", 'woops').'</li>';
		$html .= '<li>'.__("Search Engine Performance enhancement settings for larger sites.", 'woops').'</li>';
		$html .= '<li>'.__("Google Analytics Site Search Integration.", 'woops').'</li>';
		$html .= '</ul>';
		$html .= '</p>';
		$html .= '<h3>'.__('View this plugins', 'woops').' <a href="http://docs.a3rev.com/user-guides/woocommerce/woo-predictive-search/" target="_blank">'.__('documentation', 'woops').'</a></h3>';
		$html .= '<h3>'.__('Visit this plugins', 'woops').' <a href="http://wordpress.org/support/plugin/woocommerce-predictive-search/" target="_blank">'.__('support forum', 'woops').'</a></h3>';
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
