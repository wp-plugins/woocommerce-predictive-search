<?php
/**
 * WC Predictive Search WPML Functions
 *
 * Table Of Contents
 *
 * plugins_loaded()
 * wpml_register_string()
 */
class WC_Predictive_Search_WPML_Functions
{	
	public $plugin_wpml_name = 'WooCommerce Predictive Search';
	
	public function __construct() {
		
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		
		$this->wpml_ict_t();
		
	}
	
	/** 
	 * Register WPML String when plugin loaded
	 */
	public function plugins_loaded() {
		$this->wpml_register_dynamic_string();
		$this->wpml_register_static_string();
	}
	
	/** 
	 * Get WPML String when plugin loaded
	 */
	public function wpml_ict_t() {
		
		$plugin_name = 'woo_predictive_search';		
		
	}
	
	// Registry Dynamic String for WPML
	public function wpml_register_dynamic_string() {
		global $wc_ei_admin_interface;
		
		if ( function_exists('icl_register_string') ) {
			
		}
	}
	
	// Registry Static String for WPML
	public function wpml_register_static_string() {
		if ( function_exists('icl_register_string') ) {
			
			// Default Form
			icl_register_string( $this->plugin_wpml_name, 'Product Name', __( 'Product Name', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'Product SKU', __( 'Product SKU', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'Product Categories', __( 'Product Categories', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'Product Tags', __( 'Product Tags', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'Posts', __( 'Posts', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'Pages', __( 'Pages', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'SKU', __( 'SKU', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'Priced', __( 'Priced', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'Price', __( 'Price', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'Category', __( 'Category', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'Tags', __( 'Tags', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'Nothing found', __( 'Nothing found for that name. Try a different spelling or name.', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'More result Text', __( 'See more search results for', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'in', __( 'in', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'Viewing all', __( 'Viewing all', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'Search Result Text', __( 'search results for your search query', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'Sort Text', __( 'Sort Search Results by', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'Loading Text', __( 'Loading More Results...', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'No More Result Text', __( 'No More Results to Show', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'Fetching Text', __( 'Fetching search results...', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'No Fetching Result Text', __( 'No Results to Show', 'woops' ) );
			icl_register_string( $this->plugin_wpml_name, 'No Result Text', __( 'Nothing Found! Please refine your search and try again.', 'woops' ) );
		}
	}
	
}

global $wc_predictive_search_wpml;
$wc_predictive_search_wpml = new WC_Predictive_Search_WPML_Functions();

function wc_ps_ict_t_e( $name, $string ) {
	global $wc_predictive_search_wpml;
	$string = ( function_exists('icl_t') ? icl_t( $wc_predictive_search_wpml->plugin_wpml_name, $name, $string ) : $string );
	
	echo $string;
}

function wc_ps_ict_t__( $name, $string ) {
	global $wc_predictive_search_wpml;
	$string = ( function_exists('icl_t') ? icl_t( $wc_predictive_search_wpml->plugin_wpml_name, $name, $string ) : $string );
	
	return $string;
}
?>