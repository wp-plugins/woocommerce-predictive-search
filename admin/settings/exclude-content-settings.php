<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/*-----------------------------------------------------------------------------------
WC Predictive Search Exclude Content Settings

TABLE OF CONTENTS

- var parent_tab
- var subtab_data
- var option_name
- var form_key
- var position
- var form_fields
- var form_messages

- __construct()
- subtab_init()
- set_default_settings()
- get_settings()
- subtab_data()
- add_subtab()
- settings_form()
- init_form_fields()

-----------------------------------------------------------------------------------*/

class WC_PS_Exclude_Content_Settings extends WC_Predictive_Search_Admin_UI
{
	
	/**
	 * @var string
	 */
	private $parent_tab = 'exclude-content';
	
	/**
	 * @var array
	 */
	private $subtab_data;
	
	/**
	 * @var string
	 * You must change to correct option name that you are working
	 */
	public $option_name = '';
	
	/**
	 * @var string
	 * You must change to correct form key that you are working
	 */
	public $form_key = 'wc_ps_exclude_contents_settings';
	
	/**
	 * @var string
	 * You can change the order show of this sub tab in list sub tabs
	 */
	private $position = 1;
	
	/**
	 * @var array
	 */
	public $form_fields = array();
	
	/**
	 * @var array
	 */
	public $form_messages = array();
	
	/*-----------------------------------------------------------------------------------*/
	/* __construct() */
	/* Settings Constructor */
	/*-----------------------------------------------------------------------------------*/
	public function __construct() {
		$this->init_form_fields();
		$this->subtab_init();
		
		$this->form_messages = array(
				'success_message'	=> __( 'Exclude Content Settings successfully saved.', 'woops' ),
				'error_message'		=> __( 'Error: Exclude Content Settings can not save.', 'woops' ),
				'reset_message'		=> __( 'Exclude Content Settings successfully reseted.', 'woops' ),
			);
			
		add_action( $this->plugin_name . '_set_default_settings' , array( $this, 'set_default_settings' ) );

		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_init' , array( $this, 'after_save_settings' ) );
		//add_action( $this->plugin_name . '_get_all_settings' , array( $this, 'get_settings' ) );
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* subtab_init() */
	/* Sub Tab Init */
	/*-----------------------------------------------------------------------------------*/
	public function subtab_init() {
		
		add_filter( $this->plugin_name . '-' . $this->parent_tab . '_settings_subtabs_array', array( $this, 'add_subtab' ), $this->position );
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* set_default_settings()
	/* Set default settings with function called from Admin Interface */
	/*-----------------------------------------------------------------------------------*/
	public function set_default_settings() {
		global $wc_predictive_search_admin_interface;
		
		$wc_predictive_search_admin_interface->reset_settings( $this->form_fields, $this->option_name, false );
	}

	/*-----------------------------------------------------------------------------------*/
	/* after_save_settings()
	/* Process when clean on deletion option is un selected */
	/*-----------------------------------------------------------------------------------*/
	public function after_save_settings() {
		if ( ( isset( $_POST['bt_save_settings'] ) || isset( $_POST['bt_reset_settings'] ) ) )  {
			global $wc_ps_exclude_data;
			$wc_ps_exclude_data->empty_table();

			delete_option( 'woocommerce_search_exclude_products' );
			delete_option( 'woocommerce_search_exclude_posts' );
			delete_option( 'woocommerce_search_exclude_pages' );
		}
		if ( isset( $_POST['bt_save_settings'] ) )  {
			global $wc_ps_exclude_data;
			if ( isset( $_POST['woocommerce_search_exclude_products'] ) && count( $_POST['woocommerce_search_exclude_products'] ) > 0 ) {
				foreach ( $_POST['woocommerce_search_exclude_products'] as $item_id ) {
					$wc_ps_exclude_data->insert_item( $item_id, 'product' );
				}
			}
			if ( isset( $_POST['woocommerce_search_exclude_posts'] ) && count( $_POST['woocommerce_search_exclude_posts'] ) > 0 ) {
				foreach ( $_POST['woocommerce_search_exclude_posts'] as $item_id ) {
					$wc_ps_exclude_data->insert_item( $item_id, 'post' );
				}
			}
			if ( isset( $_POST['woocommerce_search_exclude_pages'] ) && count( $_POST['woocommerce_search_exclude_pages'] ) > 0 ) {
				foreach ( $_POST['woocommerce_search_exclude_pages'] as $item_id ) {
					$wc_ps_exclude_data->insert_item( $item_id, 'page' );
				}
			}
		}
	}

	/*-----------------------------------------------------------------------------------*/
	/* get_settings()
	/* Get settings with function called from Admin Interface */
	/*-----------------------------------------------------------------------------------*/
	public function get_settings() {
		global $wc_predictive_search_admin_interface;
		
		$wc_predictive_search_admin_interface->get_settings( $this->form_fields, $this->option_name );
	}
	
	/**
	 * subtab_data()
	 * Get SubTab Data
	 * =============================================
	 * array ( 
	 *		'name'				=> 'my_subtab_name'				: (required) Enter your subtab name that you want to set for this subtab
	 *		'label'				=> 'My SubTab Name'				: (required) Enter the subtab label
	 * 		'callback_function'	=> 'my_callback_function'		: (required) The callback function is called to show content of this subtab
	 * )
	 *
	 */
	public function subtab_data() {
		
		$subtab_data = array( 
			'name'				=> 'exclude-content',
			'label'				=> __( 'Exclude Content', 'woops' ),
			'callback_function'	=> 'wc_ps_exclude_content_settings_form',
		);
		
		if ( $this->subtab_data ) return $this->subtab_data;
		return $this->subtab_data = $subtab_data;
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* add_subtab() */
	/* Add Subtab to Admin Init
	/*-----------------------------------------------------------------------------------*/
	public function add_subtab( $subtabs_array ) {
	
		if ( ! is_array( $subtabs_array ) ) $subtabs_array = array();
		$subtabs_array[] = $this->subtab_data();
		
		return $subtabs_array;
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* settings_form() */
	/* Call the form from Admin Interface
	/*-----------------------------------------------------------------------------------*/
	public function settings_form() {
		global $wc_predictive_search_admin_interface;
		
		$output = '';
		$output .= $wc_predictive_search_admin_interface->admin_forms( $this->form_fields, $this->form_key, $this->option_name, $this->form_messages );
		
		return $output;
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* init_form_fields() */
	/* Init all fields of this form */
	/*-----------------------------------------------------------------------------------*/
	public function init_form_fields() {
		
		global $wpdb;
		$all_products     = array();
		$all_posts        = array();
		$all_pages        = array();

		$products_excluded     = array();
		$posts_excluded        = array();
		$pages_excluded        = array();
		
		if ( is_admin() && in_array (basename($_SERVER['PHP_SELF']), array('admin.php') ) && isset( $_GET['tab'] ) && $_GET['tab'] == 'exclude-content' ) {
			
			$results_products = $wpdb->get_results("SELECT ID, post_title FROM ".$wpdb->prefix."posts WHERE post_type='product' AND post_status='publish' ORDER BY post_title ASC");
			if ($results_products) {
				foreach($results_products as $product_data) {
					$all_products[$product_data->ID] = $product_data->post_title;
				}
			}
			$results_posts = $wpdb->get_results("SELECT ID, post_title FROM ".$wpdb->prefix."posts WHERE post_type='post' AND post_status='publish' ORDER BY post_title ASC");
			if ($results_posts) {
				foreach($results_posts as $post_data) {
					$all_posts[$post_data->ID] = $post_data->post_title;
				}
			}
			$results_pages = $wpdb->get_results("SELECT ID, post_title FROM ".$wpdb->prefix."posts WHERE post_type='page' AND post_status='publish' ORDER BY post_title ASC");
			if ($results_pages) {
				foreach($results_pages as $page_data) {
					$all_pages[$page_data->ID] = $page_data->post_title;
				}
			}

			if ( isset( $_POST['bt_save_settings'] ) )  {
				$products_excluded = array();
				if ( isset( $_POST['woocommerce_search_exclude_products'] ) ) {
					$products_excluded     = $_POST['woocommerce_search_exclude_products'];
				}
				$posts_excluded = array();
				if ( isset( $_POST['woocommerce_search_exclude_posts'] ) ) {
					$posts_excluded        = $_POST['woocommerce_search_exclude_posts'];
				}
				$pages_excluded = array();
				if ( isset( $_POST['woocommerce_search_exclude_pages'] ) ) {
					$pages_excluded        = $_POST['woocommerce_search_exclude_pages'];
				}
			} else {
				$products_excluded     = $wpdb->get_col( $wpdb->prepare( "SELECT object_id FROM {$wpdb->prefix}ps_exclude WHERE object_type = %s ", 'product' ) );
				$posts_excluded        = $wpdb->get_col( $wpdb->prepare( "SELECT object_id FROM {$wpdb->prefix}ps_exclude WHERE object_type = %s ", 'post' ) );
				$pages_excluded        = $wpdb->get_col( $wpdb->prepare( "SELECT object_id FROM {$wpdb->prefix}ps_exclude WHERE object_type = %s ", 'page' ) );
			}

		}
		
  		// Define settings			
     	$this->form_fields = apply_filters( $this->option_name . '_settings_fields', array(
		
			array(
            	'name' 		=> __( 'Exclude From Predictive Search', 'woops' ),
                'type' 		=> 'heading',
           	),
			array(  
				'name' 		=> __( 'Exclude Products', 'woops' ),
				'id' 		=> 'woocommerce_search_exclude_products',
				'type' 		=> 'multiselect',
				'placeholder' => __( 'Choose Products', 'woops' ),
				'css'		=> 'width:600px; min-height:80px;',
				'options'	=> $all_products,
				'default'	=> $products_excluded,
			),
			array(  
				'name' 		=> __( 'Exclude Posts', 'woops' ),
				'id' 		=> 'woocommerce_search_exclude_posts',
				'type' 		=> 'multiselect',
				'placeholder' => __( 'Choose Posts', 'woops' ),
				'css'		=> 'width:600px; min-height:80px;',
				'options'	=> $all_posts,
				'default'	=> $posts_excluded,
			),
			array(  
				'name' 		=> __( 'Exclude Pages', 'woops' ),
				'id' 		=> 'woocommerce_search_exclude_pages',
				'type' 		=> 'multiselect',
				'placeholder' => __( 'Choose Pages', 'woops' ),
				'css'		=> 'width:600px; min-height:80px;',
				'options'	=> $all_pages,
				'default'	=> $pages_excluded,
			),
		
        ));
	}
	
}

global $wc_ps_exclude_content_settings;
$wc_ps_exclude_content_settings = new WC_PS_Exclude_Content_Settings();

/** 
 * wc_ps_exclude_content_settings_form()
 * Define the callback function to show subtab content
 */
function wc_ps_exclude_content_settings_form() {
	global $wc_ps_exclude_content_settings;
	$wc_ps_exclude_content_settings->settings_form();
}

?>
