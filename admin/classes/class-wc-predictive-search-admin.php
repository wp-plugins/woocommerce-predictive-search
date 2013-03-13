<?php
/**
 * WC_Predictive_Search_Settings Class
 *
 * Class Function into WooCommerce plugin
 *
 * Table Of Contents
 *
 * custom_types()
 * set_setting()
 * get_id_excludes()
 * __construct()
 * on_add_tab()
 * predictive_search_global_start()
 * settings_tab_action()
 * add_settings_fields()
 * get_tab_in_view()
 * init_form_fields()
 * save_settings()
 * setting()
 * predictive_extension()
 * predictive_extension_shortcode()
 * wc_predictive_search_multi_select()
 * add_scripts()
 */
class WC_Predictive_Search_Settings {
	public function custom_types() {
		$custom_type = array('wc_predictive_search_multi_select');
		
		return $custom_type;
	}
	
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
	
	public function get_id_excludes() {
		global $wc_predictive_id_excludes;
		
		$exclude_products = get_option('woocommerce_search_exclude_products', '');
		if (is_array($exclude_products)) {
			$exclude_products = implode(",", $exclude_products);
		}
		
		$wc_predictive_id_excludes = array();
		$wc_predictive_id_excludes['exclude_products'] = $exclude_products;
		
		return $wc_predictive_id_excludes;
	}
	
	public function __construct() {
   		$this->current_tab = ( isset($_GET['tab']) ) ? $_GET['tab'] : 'general';
    	$this->settings_tabs = array(
        	'ps_settings' => __('Predictive Search', 'woops')
        );
        add_action('woocommerce_settings_tabs', array(&$this, 'on_add_tab'), 10);
		
		// add custom type to woocommerce fields
		foreach ($this->custom_types() as $custom_type) {
			add_action('woocommerce_admin_field_'.$custom_type, array(&$this, $custom_type) );
		}

        // Run these actions when generating the settings tabs.
        foreach ( $this->settings_tabs as $name => $label ) {
        	add_action('woocommerce_settings_tabs_' . $name, array(&$this, 'settings_tab_action'), 10);
			if (get_option('a3rev_woo_predictivesearch_just_confirm') == 1) {
          		update_option('a3rev_woo_predictivesearch_just_confirm', 0);
			} else {
				add_action('woocommerce_update_options_' . $name, array(&$this, 'save_settings'), 10);
			}
        }
		
		add_action( 'woocommerce_settings_predictive_search_global_start', array(&$this, 'predictive_search_global_start') );

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
	
	function predictive_search_global_start() {
		echo '<tr valign="top"><td class="forminp" colspan="2">'.__('A search results page needs to be selected so that WooCommerce Predictive Search knows where to show search results. This page should have been created upon installation of the plugin, if not you need to create it.', 'woops').'</td></tr>';
	}

    /**
     * settings_tab_action()
     *
     * Do this when viewing our custom settings tab(s). One function for all tabs.
    */
    function settings_tab_action() {
    	global $wpdb, $woocommerce_settings;
		
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
        <?php
       	do_action('woocommerce_ps_settings');
		
       	// Display settings for this tab (make sure to add the settings to the tab).
       	woocommerce_admin_fields($woocommerce_settings[$current_tab]);
		?>
        <table class="form-table"><tr valign="top"><td style="padding:0;"><div id="woo_predictive_upgrade_area"><?php echo WC_Predictive_Search_Settings::predictive_extension(); ?><div id="woo_predictive_upgrade_inner">
        <table class="form-table">
            <tr valign="top">
				<th class="titledesc" scope="row"><label for="woocommerce_search_exclude_p_categories"><?php _e('Exclude Product Categories', 'woops');?></label></th>
				<td class="forminp">
                <select multiple="multiple" name="woocommerce_search_exclude_p_categories" data-placeholder="<?php _e( 'Choose Product Categories', 'woops' ); ?>" style="display:none; width:300px;" class="chzn-select">
                <?php
				$results_p_categories = $wpdb->get_results("SELECT t.term_id, t.name FROM ".$wpdb->prefix."terms AS t INNER JOIN ".$wpdb->prefix."term_taxonomy AS tt ON(t.term_id=tt.term_id) WHERE tt.taxonomy='product_cat' ORDER BY t.name ASC");
				if ($results_p_categories) {
					foreach($results_p_categories as $p_categories_data) {
				?>
                    <option value="<?php echo $p_categories_data->term_id; ?>"><?php echo $p_categories_data->name; ?></option>
                <?php } } ?>
                </select>
                </td>
			</tr>
            <tr valign="top">
				<th class="titledesc" scope="row"><label for="woocommerce_search_exclude_p_tags"><?php _e('Exclude Product Tags', 'woops');?></label></th>
				<td class="forminp">
                <select multiple="multiple" name="woocommerce_search_exclude_p_tags" data-placeholder="<?php _e( 'Choose Product Tags', 'woops' ); ?>" style="display:none; width:300px;" class="chzn-select">
                <?php
				$results_p_tags = $wpdb->get_results("SELECT t.term_id, t.name FROM ".$wpdb->prefix."terms AS t INNER JOIN ".$wpdb->prefix."term_taxonomy AS tt ON(t.term_id=tt.term_id) WHERE tt.taxonomy='product_tag' ORDER BY t.name ASC");
				if ($results_p_tags) {
					foreach($results_p_tags as $p_tags_data) {
				?>
                    <option value="<?php echo $p_tags_data->term_id; ?>"><?php echo $p_tags_data->name; ?></option>
                <?php } } ?>
                </select>
                </td>
			</tr>
            <tr valign="top">
				<th class="titledesc" scope="row"><label for="woocommerce_search_exclude_posts"><?php _e('Exclude Posts', 'woops');?></label></th>
				<td class="forminp">
                <select multiple="multiple" name="woocommerce_search_exclude_posts" data-placeholder="<?php _e( 'Choose Posts', 'woops' ); ?>" style="display:none; width:300px;" class="chzn-select">
                <?php
				$results_posts = $wpdb->get_results("SELECT ID, post_title FROM ".$wpdb->prefix."posts WHERE post_type='post' AND post_status='publish' ORDER BY post_title ASC");
				if ($results_posts) {
					foreach($results_posts as $post_data) {
				?>
                    <option value="<?php echo $post_data->ID; ?>"><?php echo $post_data->post_title; ?></option>
                <?php } } ?>
                </select>
                </td>
			</tr>
            <tr valign="top">
				<th class="titledesc" scope="row"><label for="woocommerce_search_exclude_pages"><?php _e('Exclude Pages', 'woops');?></label></th>
				<td class="forminp">
                <select multiple="multiple" name="woocommerce_search_exclude_posts" data-placeholder="<?php _e( 'Choose Pages', 'woops' ); ?>" style="display:none; width:300px;" class="chzn-select">
                <?php
				$results_pages = $wpdb->get_results("SELECT ID, post_title FROM ".$wpdb->prefix."posts WHERE post_type='page' AND post_status='publish' ORDER BY post_title ASC");
				if ($results_pages) {
					foreach($results_pages as $page_data) {
				?>
                    <option value="<?php echo $page_data->ID; ?>"><?php echo $page_data->post_title; ?></option>
                <?php } } ?>
                </select>
                </td>
			</tr>
		</table>
        <h3 style="margin-top:0; padding-top:10px;"><?php _e('Focus Keywords', 'woops'); ?></h3>
        <table class="form-table">
          <tr valign="top">
		    <th class="titledesc" scope="row"><label for="woocommerce_search_focus_enable"><?php _e('Predictive Search', 'woops');?></label></th>
		    <td class="forminp">
              <input disabled="disabled" type="checkbox" value="1" id="woocommerce_search_focus_enable" name="woocommerce_search_focus_enable" /> <label for="woocommerce_search_focus_enable"><?php _e("Activate to optimize your sites content with Predictive Search 'Focus keywords'", 'woops');?></label>
            </td>
		  </tr>
          <tr valign="top">
		    <th class="titledesc" scope="row"><label for="woocommerce_search_focus_plugin"><?php _e("Activate SEO 'Focus Keywords'", 'woops');?></label></th>
		    <td class="forminp">
              	<select name="woocommerce_search_focus_plugin" data-placeholder="" style="display:none; width:300px;" class="chzn-select">
                    <option value="" selected="selected"><?php _e( 'Select SEO plugin', 'woops' ); ?></option>
                    <option value="yoast_seo_plugin" selected="selected"><?php _e( 'Yoast WordPress SEO', 'woops' ); ?></option>
                    <option value="all_in_one_seo_plugin" selected="selected"><?php _e( 'All in One SEO', 'woops' ); ?></option>
                </select>
            </td>
		  </tr>
        </table>
        <h3 style="margin-top:0; padding-top:10px;"><?php _e('Search results page settings', 'woops'); ?></h3>
        <table class="form-table">
          <tr valign="top">
		    <th class="titledesc" scope="row"><label for="ecommerce_search_result_items"><?php _e('Results', 'woops');?></label></th>
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
        <h3><?php _e('Predictive Search Function', 'woops'); ?></h3>
		<table class="form-table">
          <tr valign="top">
		    <td class="forminp" colspan="2">
            <?php _e('Copy and paste this global function into your themes header.php file to replace any existing search function. (Be sure to delete the existing WordPress, WooCommerce or Theme search function)', 'woops');?>
            <br /><code>&lt;?php if(function_exists('woo_predictive_search_widget')) woo_predictive_search_widget(); ?&gt;</code>
            </td>
          </tr>
        </table>
        <h3><?php _e('Customize Search Function values', 'woops');?> :</h3>		
        <table class="form-table">
			<tr valign="top">
            	<td colspan="2" class="forminp"><?php _e("The values you set here will be shown when you add the global search function to your header.php file. After adding the global function to your header.php file you can change the values here and 'Update' and they will be auto updated in the function.", "woops"); ?></td>
            </tr>
            <tr valign="top">
				<th class="titledesc" scope="row"><label for="woocommerce_search_product_items"><?php _e('Product name', 'woops');?></label></th>
				<td class="forminp"><input disabled="disabled" type="text" value="" style="width:30px;" id="woocommerce_search_product_items" name="woocommerce_search_product_items"> <span class="description"><?php _e('Number of Product Name to show in search field drop-down. Leave &lt;empty&gt; for not activated', 'woops');?></span></td>
			</tr>
			<tr valign="top">
				<th class="titledesc" scope="row"><label for="woocommerce_search_p_sku_items"><?php _e('Product SKU', 'woops');?></label></th>
				<td class="forminp"><input disabled="disabled" type="text" value="" style="width:30px;" id="woocommerce_search_p_sku_items" name="woocommerce_search_p_sku_items"> <span class="description"><?php _e('Number of Product SKU to show in search field drop-down. Leave &lt;empty&gt; for not activated', 'woops');?></span></td>
			</tr>
			<tr valign="top">
				<th class="titledesc" scope="row"><label for="woocommerce_search_p_cat_items"><?php _e('Product category', 'woops');?></label></th>
				<td class="forminp"><input disabled="disabled" type="text" value="" style="width:30px;" id="woocommerce_search_p_cat_items" name="woocommerce_search_p_cat_items"> <span class="description"><?php _e('Number of Product Categories to show in search field drop-down. Leave &lt;empty&gt; for not activated', 'woops');?></span></td>
			</tr>
            <tr valign="top">
				<th class="titledesc" scope="row"><label for="woocommerce_search_p_tag_items"><?php _e('Product tag', 'woops');?></label></th>
				<td class="forminp"><input disabled="disabled" type="text" value="" style="width:30px;" id="woocommerce_search_p_tag_items" name="woocommerce_search_p_tag_items"> <span class="description"><?php _e('Number of Product Tags to show in search field drop-down. Leave &lt;empty&gt; for not activated', 'woops');?></span></td>
			</tr>
            <tr valign="top">
				<th class="titledesc" scope="row"><label for="woocommerce_search_post_items"><?php _e('Post', 'woops');?></label></th>
				<td class="forminp"><input disabled="disabled" type="text" value="" style="width:30px;" id="woocommerce_search_post_items" name="woocommerce_search_post_items"> <span class="description"><?php _e('Number of Posts to show in search field drop-down. Leave &lt;empty&gt; for not activated', 'woops');?></span></td>
			</tr>
            <tr valign="top">
				<th class="titledesc" scope="row"><label for="woocommerce_search_page_items"><?php _e('Page', 'woops');?></label></th>
				<td class="forminp"><input disabled="disabled" type="text" value="" style="width:30px;" id="woocommerce_search_page_items" name="woocommerce_search_page_items"> <span class="description"><?php _e('Number of Pages to show in search field drop-down. Leave &lt;empty&gt; for not activated', 'woops');?></span></td>
			</tr>
            <tr valign="top">
				<th class="titledesc" scope="row"><label for="woocommerce_search_character_max"><?php _e('Description Characters', 'woops');?></label></th>
				<td class="forminp"><input disabled="disabled" type="text" value="" style="width:30px;" id="woocommerce_search_character_max" name="woocommerce_search_character_max"> <span class="description"><?php _e('Number of characters from product description to show in search field drop-down. Default value is "100".', 'woops');?></span></td>
			</tr>
            <tr valign="top">
				<th class="titledesc" scope="row"><label for="woocommerce_search_width"><?php _e('Width', 'woops');?></label></th>
				<td class="forminp"><input disabled="disabled" type="text" value="" style="width:30px;" id="woocommerce_search_width" name="woocommerce_search_width"> <span class="description">px. <?php _e('Leave &lt;empty&gt; for 100% wide', 'woops');?></span></td>
			</tr>
            <tr valign="top">
				<th class="titledesc" scope="row"><label for="woocommerce_search_padding_top"><?php _e('Padding top', 'woops');?></label></th>
				<td class="forminp"><input disabled="disabled" type="text" value="" style="width:30px;" id="woocommerce_search_padding_top" name="woocommerce_search_padding_top"> <span class="description">px</span></td>
			</tr>
            <tr valign="top">
				<th class="titledesc" scope="row"><label for="woocommerce_search_padding_bottom"><?php _e('Padding bottom', 'woops');?></label></th>
				<td class="forminp"><input disabled="disabled" type="text" value="" style="width:30px;" id="woocommerce_search_padding_bottom" name="woocommerce_search_padding_bottom"> <span class="description">px</span></td>
			</tr>
            <tr valign="top">
				<th class="titledesc" scope="row"><label for="woocommerce_search_padding_left"><?php _e('Padding lef', 'woops');?>t</label></th>
				<td class="forminp"><input disabled="disabled" type="text" value="" style="width:30px;" id="woocommerce_search_padding_left" name="woocommerce_search_padding_left"> <span class="description">px</span></td>
			</tr>
            <tr valign="top">
				<th class="titledesc" scope="row"><label for="woocommerce_search_padding_right"><?php _e('Padding right', 'woops');?></label></th>
				<td class="forminp"><input disabled="disabled" type="text" value="" style="width:30px;" id="woocommerce_search_padding_right" name="woocommerce_search_padding_right"> <span class="description">px</span></td>
			</tr>
            <tr valign="top">
				<th class="titledesc" scope="row"><label for="woocommerce_search_custom_style"><?php _e('Custom style', 'woops');?></label></th>
				<td class="forminp"><input disabled="disabled" type="text" value="" style="min-width:300px;" id="woocommerce_search_custom_style" name="woocommerce_search_custom_style"> <p class="description"><?php _e('Put other custom style for the Predictive search box', 'woops');?></p></td>
			</tr>
            <tr valign="top" class="">
				<th class="titledesc" scope="row"><?php _e('Global search', 'woops');?></th>
				<td class="forminp">
					<fieldset><legend class="screen-reader-text"><span><?php _e('Global search', 'woops');?></span></legend>
						<label for="woocommerce_search_global_search">
						<input disabled="disabled" checked="checked" type="checkbox" value="1" id="woocommerce_search_global_search" name="woocommerce_search_global_search">
						<?php _e('Set global search or search in current product category or current product tag. "Checked" to activate global search.', 'woops');?></label> <br>
					</fieldset>
				</td>
			</tr>
		</table>
        </div></div></td></tr></table>
        <?php
		add_action('admin_footer', array(&$this, 'add_scripts'), 10);
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
		$all_products = array();
		$results_products = $wpdb->get_results("SELECT ID, post_title FROM ".$wpdb->prefix."posts WHERE post_type='product' AND post_status='publish' ORDER BY post_title ASC");
		if ($results_products) {
			foreach($results_products as $product_data) {
				$all_products[$product_data->ID] = $product_data->post_title;
			}
		}
		
  		// Define settings			
     	$this->fields['ps_settings'] = apply_filters('woocommerce_ps_settings_fields', array(
			array(
            	'name' => __( 'Global Search Box Text', 'woops' ),
                'type' => 'title',
                'desc' => '',
          		'id' => 'predictive_search_searchbox_text_start'
           	),
			array(  
				'name' => __( 'Text to Show', 'woops' ),
				'desc' 		=> __('&lt;empty&gt; shows nothing', 'woops'),
				'id' 		=> 'woocommerce_search_box_text',
				'type' 		=> 'text',
				'css' 		=> 'min-width:300px;',
				'std' 		=> ''
			),
			array('type' => 'sectionend', 'id' => 'predictive_search_searchbox_text_end'),
			
			array(
            	'name' => __('Global Settings', 'woops'),
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
			array('type' => 'sectionend', 'id' => 'predictive_search_global_end'),
			
			array(
            	'name' => __( 'Exclude From Predictive Search', 'woops' ),
                'type' => 'title',
                'desc' => '',
          		'id' => 'predictive_search_excludes_start'
           	),
			array(  
				'name' => __( 'Exclude Products', 'woops' ),
				'desc' 		=> '',
				'id' 		=> 'woocommerce_search_exclude_products',
				'type' 		=> 'wc_predictive_search_multi_select',
				'std' 		=> '',
				'placeholder' => __( 'Choose Products', 'woops' ),
				'options'	=> $all_products,
			),
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
		
		update_option('woocommerce_search_exclude_products', (array) $_REQUEST['woocommerce_search_exclude_products']);
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
		$html .= '<a href="http://a3rev.com/shop/" target="_blank" style="float:right;margin-top:5px; margin-left:10px;" ><img src="'.WOOPS_IMAGES_URL.'/a3logo.png" /></a>';
		$html .= '<h3>'.__('Upgrade to Predictive Search Pro', 'woops').'</h3>';
		$html .= '<p>'.__("Visit the", 'woops').' <a href="'.WOOPS_AUTHOR_URI.'" target="_blank">'.__("a3rev website", 'woops').'</a> '.__("to see all the extra features the Pro version of this plugin offers like", 'woops').':</p>';
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
		$html .= '</ul>';
		$html .= '</p>';
		$html .= '<h3>'.__('Plugin Documentation', 'woops').'</h3>';
		$html .= '<p>'.__('All of our plugins have comprehensive online documentation. Please refer to the plugins docs before raising a support request', 'woops').'. <a href="http://docs.a3rev.com/user-guides/woocommerce/woo-predictive-search/" target="_blank">'.__('Visit the a3rev wiki.', 'woops').'</a></p>';
		$html .= '<h3>'.__('More a3rev Quality Plugins', 'woops').'</h3>';
		$html .= '<p>'.__('Below is a list of the a3rev plugins that are available for free download from wordpress.org', 'woops').'</p>';
		$html .= '<h3>'.__('WooCommerce Plugins', 'woops').'</h3>';
		$html .= '<p>';
		$html .= '<ul style="padding-left:10px;">';
		$html .= '<li>* <a href="http://wordpress.org/extend/plugins/woocommerce-dynamic-gallery/" target="_blank">'.__('WooCommerce Dynamic Products Gallery', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/extend/plugins/woocommerce-predictive-search/" target="_blank">'.__('WooCommerce Predictive Search', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/extend/plugins/woocommerce-compare-products/" target="_blank">'.__('WooCommerce Compare Products', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/extend/plugins/woo-widget-product-slideshow/" target="_blank">'.__('WooCommerce Widget Product Slideshow', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/extend/plugins/woocommerce-email-inquiry-cart-options/" target="_blank">'.__('WooCommerce Email Inquiry & Cart Options', 'woops').'</a></li>';
		$html .= '</ul>';
		$html .= '</p>';
		$html .= '<h3>'.__('WordPress Plugins', 'woops').'</h3>';
		$html .= '<p>';
		$html .= '<ul style="padding-left:10px;">';
		$html .= '<li>* <a href="http://wordpress.org/extend/plugins/wp-email-template/" target="_blank">'.__('WordPress Email Template', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/extend/plugins/page-views-count/" target="_blank">'.__('Page View Count', 'woops').'</a></li>';
		$html .= '</ul>';
		$html .= '</p>';
		$html .= '<h3>'.__('WP e-Commerce Plugins', 'woops').'</h3>';
		$html .= '<p>';
		$html .= '<ul style="padding-left:10px;">';
		$html .= '<li>* <a href="http://wordpress.org/extend/plugins/wp-e-commerce-dynamic-gallery/" target="_blank">'.__('WP e-Commerce Dynamic Gallery', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/extend/plugins/wp-e-commerce-predictive-search/" target="_blank">'.__('WP e-Commerce Predictive Search', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/extend/plugins/wp-ecommerce-compare-products/" target="_blank">'.__('WP e-Commerce Compare Products', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/extend/plugins/wp-e-commerce-catalog-visibility-and-email-inquiry/" target="_blank">'.__('WP e-Commerce Catalog Visibility & Email Inquiry', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/extend/plugins/wp-e-commerce-grid-view/" target="_blank">'.__('WP e-Commerce Grid View', 'woops').'</a></li>';
		$html .= '</ul>';
		$html .= '</p>';
		$html .= '<h3>'.__('Help spread the Word about this plugin', 'woops').'</h3>';
		$html .= '<p>'.__("Things you can do to help others find this plugin", 'woops');
		$html .= '<ul style="padding-left:10px;">';
		$html .= '<li>* <a href="http://wordpress.org/extend/plugins/woocommerce-predictive-search/" target="_blank">'.__('Rate this plugin 5', 'woops').' <img src="'.WOOPS_IMAGES_URL.'/stars.png" align="top" /> '.__('on WordPress.org', 'woops').'</a></li>';
		$html .= '<li>* <a href="http://wordpress.org/extend/plugins/woocommerce-predictive-search/" target="_blank">'.__('Mark the plugin as a fourite', 'woops').'</a></li>';
		$html .= '</ul>';
		$html .= '</p>';
		$html .= '</div>';
		return $html;
	}
	
	function predictive_extension_shortcode() {
		$html = '';
		$html .= '<div id="woo_predictive_extensions">'.__("Yes you'll love the Predictive Search shortcode feature. Upgrading to the", 'woops').' <a target="_blank" href="'.WOOPS_AUTHOR_URI.'">'.__('Pro Version', 'woops').'</a> '.__("activates this shortcode feature as well as the awesome 'Smart Search' feature, per widget controls, the All Search Results page customization settings and function features.", 'woops').'</div>';
		return $html;	
	}
	
	function wc_predictive_search_multi_select($value) {
		if ( $value['desc_tip'] === true ) {
    		$description = '<img class="help_tip" data-tip="' . esc_attr( $value['desc'] ) . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" />';
    	} elseif ( $value['desc_tip'] ) {
    		$description = '<img class="help_tip" data-tip="' . esc_attr( $value['desc_tip'] ) . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" />';
    	} else {
    		$description = '<span class="description">' . $value['desc'] . '</span>';
    	}
		$selections = (array) get_option($value['id']);
	?>
    	<tr valign="top">
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo $value['name']; ?></label>
            </th>
            <td class="forminp">
                <select multiple="multiple" name="<?php echo esc_attr( $value['id'] ); ?>[]" data-placeholder="<?php echo $value['placeholder']; ?>" style="display:none; width:600px; <?php echo esc_attr( $value['css'] ); ?>" class="chzn-select <?php if (isset($value['class'])) echo $value['class']; ?>">
                <?php
                foreach ($value['options'] as $key => $val) {
                ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( in_array($key, $selections), true ); ?>><?php echo $val ?></option>
                <?php
                }
                ?>
                </select> <?php echo $description; ?>
            </td>
		</tr>
    <?php
	}
	
	function add_scripts(){
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script('jquery');
		
		wp_enqueue_style( 'a3rev-chosen-style', WOOPS_JS_URL . '/chosen/chosen.css' );
		wp_enqueue_script( 'chosen', WOOPS_JS_URL . '/chosen/chosen.jquery'.$suffix.'.js', array(), false, true );
		
		wp_enqueue_script( 'a3rev-chosen-script-init', WOOPS_JS_URL.'/init-chosen.js', array(), false, true );
	}
}
?>