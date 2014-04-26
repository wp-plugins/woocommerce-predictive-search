<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/*-----------------------------------------------------------------------------------
WC Predictive Search Google Analytics Settings

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

class WC_Predictive_Search_Google_Analytics_Settings extends WC_Predictive_Search_Admin_UI
{
	
	/**
	 * @var string
	 */
	private $parent_tab = 'google-analytics';
	
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
	public $form_key = 'wc_predictive_search_google_analytics_settings';
	
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
				'success_message'	=> __( 'Google Analytics Settings successfully saved.', 'woops' ),
				'error_message'		=> __( 'Error: Google Analytics Settings can not save.', 'woops' ),
				'reset_message'		=> __( 'Google Analytics Settings successfully reseted.', 'woops' ),
			);
			
		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_end', array( $this, 'include_script' ) );
			
		add_action( $this->plugin_name . '_set_default_settings' , array( $this, 'set_default_settings' ) );
		
		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_init' , array( $this, 'reset_default_settings' ) );
				
		//add_action( $this->plugin_name . '_get_all_settings' , array( $this, 'get_settings' ) );
		
		add_action( $this->plugin_name . '-'. $this->form_key.'_settings_start', array( $this, 'pro_fields_before' ) );
		add_action( $this->plugin_name . '-'. $this->form_key.'_settings_end', array( $this, 'pro_fields_after' ) );
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
	/* reset_default_settings()
	/* Reset default settings with function called from Admin Interface */
	/*-----------------------------------------------------------------------------------*/
	public function reset_default_settings() {
		global $wc_predictive_search_admin_interface;
		
		$wc_predictive_search_admin_interface->reset_settings( $this->form_fields, $this->option_name, true, true );
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
			'name'				=> 'google-analytics-settings',
			'label'				=> __( 'Google Analytics', 'woops' ),
			'callback_function'	=> 'wc_predictive_search_google_analytics_settings_form',
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
		
  		// Define settings			
     	$this->form_fields = apply_filters( $this->option_name . '_settings_fields', array(
		
			array(
            	'name' 		=> __( 'Google Analytics Site Search Integration', 'woops' ),
                'type' 		=> 'heading',
           	),
			array(  
				'name' 		=> __( 'Track Predictive Search Result with Google Analytics', 'woops' ),
				'class'		=> 'woocommerce_search_enable_google_analytic',
				'id' 		=> 'woocommerce_search_enable_google_analytic',
				'type' 		=> 'onoff_checkbox',
				'default'	=> 'yes',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'woops' ),
				'unchecked_label' 	=> __( 'OFF', 'woops' ),
			),
			
			array(
                'type' 		=> 'heading',
				'class'		=> 'woocommerce_search_enable_google_analytic_container',
           	),
			array(  
				'name' 		=> __( 'Google Analytics UID', 'woops' ),
				'desc' 		=> __('Example:', 'woops') . ' UA-3423237-10',
				'id' 		=> 'woocommerce_search_google_analytic_id',
				'type' 		=> 'text',
				'custom_attributes'	=> array( 'placeholder' => 'UA-XXXX-Y' ),
				'default'	=> ''
			),
			array(  
				'name' 		=> __( 'Query Parameter', 'woops' ),
				'desc' 		=> __( 'The parameter that is to be entered on the track Site Search config page on your Google Anayitics account. Default: [default_value]', 'woops' ),
				'id' 		=> 'woocommerce_search_google_analytic_query_parameter',
				'type' 		=> 'text',
				'default'	=> 'ps'
			),
		
        ));
	}
	
	public function include_script() {
	?>
<script>
(function($) {
	
	$(document).ready(function() {
		
		if ( $("input.woocommerce_search_enable_google_analytic:checked").val() == 'yes') {
			$(".woocommerce_search_enable_google_analytic_container").show();
		} else {
			$(".woocommerce_search_enable_google_analytic_container").hide();
		}
			
		$(document).on( "a3rev-ui-onoff_checkbox-switch", '.woocommerce_search_enable_google_analytic', function( event, value, status ) {
			if ( status == 'true' ) {
				$(".woocommerce_search_enable_google_analytic_container").slideDown();
			} else {
				$(".woocommerce_search_enable_google_analytic_container").slideUp();
			}
		});
		
	});
	
})(jQuery);
</script>
    <?php	
	}
}

global $wc_predictive_search_google_analytics_settings;
$wc_predictive_search_google_analytics_settings = new WC_Predictive_Search_Google_Analytics_Settings();

/** 
 * wc_predictive_search_google_analytics_settings_form()
 * Define the callback function to show subtab content
 */
function wc_predictive_search_google_analytics_settings_form() {
	global $wc_predictive_search_google_analytics_settings;
	$wc_predictive_search_google_analytics_settings->settings_form();
}

?>