<?php
/**
 * WC_Predictive_Search_Settings Class
 *
 * Class Function into WooCommerce plugin
 *
 * Table Of Contents
 *
 * set_setting()
 * __construct()
 * on_add_tab()
 * settings_tab_action()
 * add_settings_fields()
 * get_tab_in_view()
 * init_form_fields()
 * save_settings()
 * setting()
 * predictive_extension()
 * predictive_extension_shortcode()
 */
class WC_Predictive_Search_Settings {
	public function set_setting($reset=false){
		if ( get_option('woocommerce_search_text_lenght') <= 0 || $reset ) {
			update_option('woocommerce_search_text_lenght','100');
		}
		if ( get_option('woocommerce_search_result_items') <= 0 || $reset ) {
			update_option('woocommerce_search_result_items','10');
		}
		if ( get_option('woocommerce_search_price_enable') == '' || $reset ) {
			update_option('woocommerce_search_price_enable', 'no');
		}
		if ( get_option('woocommerce_search_addtocart_enable') == '' || $reset ) {
			update_option('woocommerce_search_addtocart_enable', 'yes');
		}
		if ( get_option('woocommerce_search_categories_enable') == ''  || $reset ) {
			update_option('woocommerce_search_categories_enable', 'no');
		}
		if ( get_option('woocommerce_search_tags_enable') == '' || $reset ) {
			update_option('woocommerce_search_tags_enable', 'no');
		}
	}
	
	public function __construct() {
   		$this->current_tab = ( isset($_GET['tab']) ) ? $_GET['tab'] : 'general';
    	$this->settings_tabs = array(
        	'ps_settings' => __('Predictive Search', 'woops')
        );
        add_action('woocommerce_settings_tabs', array(&$this, 'on_add_tab'), 10);

        // Run these actions when generating the settings tabs.
        foreach ( $this->settings_tabs as $name => $label ) {
        	add_action('woocommerce_settings_tabs_' . $name, array(&$this, 'settings_tab_action'), 10);
			if (get_option('a3rev_woo_predictivesearch_just_confirm') == 1) {
          		update_option('a3rev_woo_predictivesearch_just_confirm', 0);
			} else {
				add_action('woocommerce_update_options_' . $name, array(&$this, 'save_settings'), 10);
			}
        }
		
		add_action( 'woocommerce_settings_predictive_search_code_start', array(&$this, 'predictive_search_code_start') );
		add_action( 'woocommerce_settings_predictive_search_code_end', array(&$this, 'predictive_search_code_end') );

        // Add the settings fields to each tab.
        add_action('woocommerce_ps_settings', array(&$this, 'add_settings_fields'), 10);
				
	}

    /*
    * Admin Functions
    */

    /* ----------------------------------------------------------------------------------- */
    /* Admin Tabs */
    /* ----------------------------------------------------------------------------------- */
	function on_add_tab() {
    	foreach ( $this->settings_tabs as $name => $label ) :
        	$class = 'nav-tab';
      		if ( $this->current_tab == $name )
            	$class .= ' nav-tab-active';
      		echo '<a href="' . admin_url('admin.php?page=woocommerce&tab=' . $name) . '" class="' . $class . '">' . $label . '</a>';
     	endforeach;
	}

    /**
     * settings_tab_action()
     *
     * Do this when viewing our custom settings tab(s). One function for all tabs.
    */
    function settings_tab_action() {
    	global $woocommerce_settings;
		
		// Determine the current tab in effect.
        $current_tab = $this->get_tab_in_view(current_filter(), 'woocommerce_settings_tabs_');

        // Hook onto this from another function to keep things clean.
        // do_action( 'woocommerce_newsletter_settings' );

		?>
        <style type="text/css">
		.form-table { margin:0; }
		#woo_predictive_upgrade_area { border:2px solid #E6DB55;-webkit-border-radius:10px;-moz-border-radius:10px;-o-border-radius:10px; border-radius: 10px; padding:0 40% 0 0; position:relative; background:#FFFBCC;}
		#woo_predictive_upgrade_inner { background:#FFF; -webkit-border-radius:10px 0 0 10px;-moz-border-radius:10px 0 0 10px;-o-border-radius:10px 0 0 10px; border-radius: 10px 0 0 10px;}
		#woo_predictive_upgrade_inner h3{ margin-left:10px;}
		#woo_predictive_extensions { -webkit-border-radius:4px;-moz-border-radius:4px;-o-border-radius:4px; border-radius: 4px 4px 4px 4px; color: #555555; float: right; margin: 0px; padding: 5px; position: absolute; text-shadow: 0 1px 0 rgba(255, 255, 255, 0.8); width: 38%; right:0; top:0px;}		
        </style>
        <h3><?php _e('Global Settings', 'woops'); ?></h3>
        <table class="form-table">
          <tr valign="top">
		    <td class="forminp">
            <?php _e('A search results page needs to be selected so that WooCommerce Predictive Search knows where to show search results. This page should have been created upon installation of the plugin, if not you need to create it.', 'woops');?>
            </td>
          </tr>
		</table>
        <?php
       	do_action('woocommerce_ps_settings');
		
       	// Display settings for this tab (make sure to add the settings to the tab).
       	woocommerce_admin_fields($woocommerce_settings[$current_tab]);
		?>
        <table class="form-table"><tr valign="top"><td style="padding:0;"><div id="woo_predictive_upgrade_area"><?php echo WC_Predictive_Search_Settings::predictive_extension(); ?><div id="woo_predictive_upgrade_inner">
        <h3 style="margin-top:0; padding-top:10px;"><?php _e('Search results page settings', 'woops'); ?></h3>
        <table class="form-table">
          <tr valign="top">
		    <th class="titledesc" scope="row"><label for="ecommerce_search_result_items"><?php _e('Results', 'woops');?>	</label></th>
		    <td class="forminp">
              <input disabled="disabled" type="text" value="10" size="6" id="ecommerce_search_result_items" name="ecommerce_search_result_items" />
              <span class="description"><?php _e('The number of results to show before endless scroll click to see more results.', 'woops');?></span>
            </td>
		  </tr>
		  <tr valign="top">
		    <th class="titledesc" scope="row"><label for="ecommerce_search_text_lenght"><?php _e('Description character count', 'woops');?></label></th>
		    <td class="forminp">
              <input disabled="disabled" type="text" value="100" size="6" id="ecommerce_search_text_lenght" name="ecommerce_search_text_lenght" />
              <span class="description"><?php _e('The number of characters from product descriptions that shows with each search result.', 'woops');?></span>
            </td>
		  </tr>
          <tr valign="top">
		    <th class="titledesc" scope="row"><label for="ecommerce_search_price_enable"><?php _e('Price', 'woops');?></label></th>
		    <td class="forminp">
              <input disabled="disabled" type="checkbox" value="1" id="ecommerce_search_price_enable" name="ecommerce_search_price_enable" /> <span class="description"><?php _e('Show product price with search results', 'woops');?></span>
            </td>
		  </tr>
          <tr valign="top">
		    <th class="titledesc" scope="row"><label for="woocommerce_search_addtocart_enable"><?php _e('Add to cart', 'woops');?></label></th>
		    <td class="forminp">
              <input disabled="disabled" type="checkbox" value="1" id="woocommerce_search_addtocart_enable" name="woocommerce_search_addtocart_enable" /> <span class="description"><?php _e('Show Add to cart button with search results', 'woops');?></span>
            </td>
		  </tr>
          <tr valign="top">
		    <th class="titledesc" scope="row"><label for="ecommerce_search_categories_enable"><?php _e('Product Categories', 'woops');?></label></th>
		    <td class="forminp">
              <input disabled="disabled" type="checkbox" value="1" id="ecommerce_search_categories_enable" name="ecommerce_search_categories_enable" /> <span class="description"><?php _e('Show categories with search results', 'woops');?></span>
            </td>
		  </tr>
          <tr valign="top">
		    <th class="titledesc" scope="row"><label for="ecommerce_search_tags_enable"><?php _e('Product Tags', 'woops');?></label></th>
		    <td class="forminp">
              <input disabled="disabled" type="checkbox" value="1" id="ecommerce_search_tags_enable" name="ecommerce_search_tags_enable" /> <span class="description"><?php _e('Show tags with search results', 'woops');?></span>
            </td>
		  </tr>
        </table>
        <h3><?php _e('Code', 'woops'); ?></h3>
		<table class="form-table">
          <tr valign="top">
		    <td class="forminp" colspan="2">
            <?php _e('Use this function to place the Predictive Search feature anywhere in your theme.', 'woops');?>
            <br /><code>&lt;?php if(function_exists('woo_predictive_search_widget')) woo_predictive_search_widget($product_name_items, $product_sku_items, $product_category_items, $product_tag_items, $post_items, $page_items, $character_max, $style, $global_search); ?&gt;</code>
            <br /><br />
            <p><?php _e('Parameters', 'woops');?> :
            <br /><code>$product_name_items (int)</code> : <?php _e('Number of Product Name to show in search field drop-down. Default value is "6".', 'woops');?>
            <br /><code>$product_sku_items (int)</code> : <?php _e('Number of Product SKU to show in search field drop-down. Default value is "0".', 'woops');?>
            <br /><code>$product_category_items (int)</code> : <?php _e('Number of Product Categories to show in search field drop-down. Default value is "0".', 'woops');?>
            <br /><code>$product_tag_items (int)</code> : <?php _e('Number of Product Tags to show in search field drop-down. Default value is "0".', 'woops');?>
            <br /><code>$post_items (int)</code> : <?php _e('Number of Posts to show in search field drop-down. Default value is "0".', 'woops');?>
            <br /><code>$page_items (int)</code> : <?php _e('Number of Pages to show in search field drop-down. Default value is "0".', 'woops');?>
            <br /><code>$character_max (int)</code> : <?php _e('Number of characters from product description to show in search field drop-down. Default value is "100".', 'woops');?>
            <br /><code>$style (string)</code> : <?php _e('Use to create a custom style for the Predictive search box | Example: ', 'woops');?><code>"padding-top:10px;padding-bottom:10px;padding-left:0px;padding-right:0px;"</code>
            <br /><code>$global_search (bool)</code> : <?php _e('Set global search or search in current product category or current product tag. Default value is "true", global search.', 'woops');?>
            </p>
            </td>
          </tr>
        </table>
        </div></div></td></tr></table>
        <?php
	}

	/**
     * add_settings_fields()
     *
     * Add settings fields for each tab.
    */
    function add_settings_fields() {
    	global $woocommerce_settings;

        // Load the prepared form fields.
        $this->init_form_fields();

        if ( is_array($this->fields) ) :
        	foreach ( $this->fields as $k => $v ) :
                $woocommerce_settings[$k] = $v;
            endforeach;
        endif;
	}

    /**
    * get_tab_in_view()
    *
    * Get the tab current in view/processing.
    */
    function get_tab_in_view($current_filter, $filter_base) {
    	return str_replace($filter_base, '', $current_filter);
    }

    /**
     * init_form_fields()
     *
     * Prepare form fields to be used in the various tabs.
     */
	function init_form_fields() {
		global $wpdb;
  		// Define settings			
     	$this->fields['ps_settings'] = apply_filters('woocommerce_ps_settings_fields', array(
      		array(
            	'name' => '',
                'type' => 'title',
                'desc' => '',
          		'id' => 'predictive_search_global_start'
           	),
			array(  
				'name' => __( 'Search Page', 'woops' ),
				'desc' 		=> __('Page contents:', 'woops').' [woocommerce_search]',
				'id' 		=> 'woocommerce_search_page_id',
				'type' 		=> 'single_select_page',
				'std' 		=> '',
				'class'		=> 'chosen_select_nostd',
				'css' 		=> 'min-width:300px;',
				'desc_tip'	=>  false
			),
			array('type' => 'sectionend', 'id' => 'predictive_search_global_end')
        ));
	}

    /**
     * save_settings()
     *
     * Save settings in a single field in the database for each tab's fields (one field per tab).
     */
     function save_settings() {
     	global $woocommerce_settings;

        // Make sure our settings fields are recognised.
        $this->add_settings_fields();

        $current_tab = $this->get_tab_in_view(current_filter(), 'woocommerce_update_options_');

		woocommerce_update_options($woocommerce_settings[$current_tab]);
		WC_Predictive_Search_Settings::set_setting(true);
	}

    /** Helper functions ***************************************************** */
         
    /**
     * Gets a setting
     */
    public function setting($key) {
		return get_option($key);
	}
	
	function predictive_extension() {
		$html = '';
		$html .= '<div id="woo_predictive_extensions">';
		$html .= '<h3>'.__('No Donations Accepted', 'woops').'</h3>';
		$html .= '<img src="'.WOOPS_IMAGES_URL.'/btn_donate.png" />';
		$html .= '<h3>'.__('Upgrade to the Pro version for Just', '').' $20 '.__('to', 'woops').'</h3>';
		$html .= '<p>';
		$html .= '<ul style="padding-left:10px;">';
		$html .= '<li>1. '.__('Activate the search results pages settings in this yellow border.', 'woops').'</li>';
		$html .= '<li>2. '.__('Activate Search by Product Categories, Product Tags, Posts and Pages options in the search widgets.', 'woops').'</li>';
		$html .= '<li>3. '.__('Activate Search shortcodes for Posts and pages.', 'woops').'</li>';
		$html .= '<li>4. '.__('Same day priority support.', 'woops').'</li>';
		$html .= '</ul>';
		$html .= '</p>';
		$html .= '<p>* '.__('See the Pro version on the', 'woops').' <a href="'.WOOPS_AUTHOR_URI.'" target="_blank">'.__('A3 market place', 'woops').'</a></p>';
		$html .= '<h3>'.__('Go Pro and help us help you.', 'woops').'</h3>';
		$html .= '<p>'.__('A Pro upgrade license fee helps fund and support the maintenance and ongoing development of this plugin.', 'woops').'</p>';
		$html .= '<h3>'.__('More WooCommerce Plugins from A3 Rev', 'woops').'</h3>';
		$html .= '<p>';
		$html .= '<ul style="padding-left:10px;">';
		$html .= '<li>* <a href="http://wordpress.org/extend/plugins/woocommerce-dynamic-gallery/" target="_blank">'.__('WooCommerce Dynamic Products Gallery', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/extend/plugins/woocommerce-compare-products/" target="_blank">'.__('WooCommerce Compare Products', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/extend/plugins/wp-email-template/" target="_blank">'.__('WooCommerce Global Email Template', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://a3rev.com/products-page/woocommerce/woo-email-inquiry-and-cart-options/" target="_blank">'.__('WooCommerce Email Inquiry & Cart View (Pro only)', 'woops').'</a></li>';
		$html .= '</ul>';
		$html .= '</p>';
		$html .= '<h3>'.__('Spreading the Word about this plugin.', 'woops').'</h3>';
		$html .= '<p>'.__("Things you can do to help others find this plugin", 'woops');
		$html .= '<ul style="padding-left:10px;">';
		$html .= '<li>* <a href="http://wordpress.org/extend/plugins/woocommerce-predictive-search/" target="_blank">'.__('Rate this plugin 5', 'woops').' <img src="'.WOOPS_IMAGES_URL.'/stars.png" align="top" /> '.__('on WordPress.org', 'woops').'</a></li>';
		$html .= '<li>* <a href="'.WOOPS_AUTHOR_URI.'" target="_blank">'.__('Write about it in your blog', 'woops').'</a></li>';
		$html .= '</ul>';
		$html .= '</p>';
		$html .= '<h3>'.__('Thank you for your support!', 'woops').'</h3>';
		$html .= '</div>';
		return $html;	
	}
	
	function predictive_extension_shortcode() {
		$html = '';
		$html .= '<div id="woo_predictive_extensions">'.__("Yes you'll love the Predictive Search shortcode feature. Upgrading to the", 'woops').' <a target="_blank" href="'.WOOPS_AUTHOR_URI.'">'.__('Pro Version', 'woops').'</a> '.__("activates this shortcode feature as well as the awesome 'Smart Search' feature, per widget controls, the All Search Results page customization settings and function features.", 'woops').'</div>';
		return $html;	
	}
}
?>