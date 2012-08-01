<?php
/**
 * WooCommerce Predictive Search Widget
 *
 * Table Of Contents
 *
 * __construct()
 * widget()
 * woops_results_search_form()
 * update()
 * form()
 */
class WC_Predictive_Search_Widgets extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget_products_predictive_search', 'description' => __( 'User sees search results as they type. Shows top 6 results and links through to search results page.', 'woops') );
		parent::__construct('products_predictive_search', __('WooCommerce Predictive Search', 'woops'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
		if(empty($instance['number_items']) || $instance['number_items'] <= 0) $number_items = 6; 
		else $number_items = $instance['number_items'];
		if(empty($instance['text_lenght']) || $instance['text_lenght'] < 0) $text_lenght = 100; 
		else $text_lenght = $instance['text_lenght'];
		$search_global = empty($instance['search_global']) ? 0 : $instance['search_global'];

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		$this->woops_results_search_form($widget_id, $number_items, $text_lenght, '',$search_global);
		echo $after_widget;
	}
	
	function woops_results_search_form($widget_id, $number_items=6, $text_lenght=100, $style='', $search_global = 0) {
		
		// Add ajax search box script and style at footer
		add_action('wp_footer',array('WC_Predictive_Search_Hook_Filter','add_frontend_script'));
		
		$id = str_replace('products_predictive_search-','',$widget_id);
		$woops_get_result_popup = wp_create_nonce("woops-get-result-popup");
		$cat_slug = '';
		$tag_slug = '';
		$row = 6;
		if ( $number_items > 0  ) $row = $number_items;
		?>
        <script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery("#bt_pp_search_<?php echo $id;?>").click(function(){
				jQuery("#fr_pp_search_widget_<?php echo $id;?>").submit();
			});
			var ul_width = jQuery("#pp_search_container_<?php echo $id;?>").width();
			var ul_height = jQuery("#pp_search_container_<?php echo $id;?>").height();
			
			var urls = '<?php echo admin_url('admin-ajax.php');?>'+'?action=woops_get_result_popup';
			
            jQuery("#pp_course_<?php echo $id;?>").autocomplete(urls, {
                width: ul_width,
    			scrollHeight: 2000,
				max: <?php echo ($row + 2); ?>,
				extraParams: {'row':'<?php echo $row; ?>', 'text_lenght':'<?php echo $text_lenght;?>', 'security':'<?php echo $woops_get_result_popup;?>' <?php if($cat_slug != ''){ ?>, 'scat':'<?php echo $cat_slug ?>' <?php } ?> <?php if($tag_slug != ''){ ?>, 'stag':'<?php echo $tag_slug ?>' <?php } ?> },
				inputClass: "ac_input_<?php echo $id; ?>",
				resultsClass: "ac_results_<?php echo $id; ?>",
				loadingClass: "predictive_loading",
				highlight : false,
            });
            jQuery("#pp_course_<?php echo $id;?>").result(function(event, data, formatted) {
				if(data[2] != ''){
					jQuery("#pp_course_<?php echo $id;?>").val(data[2]);
				}
				window.location.href(data[1]);
            });
        });
        </script>
        <div class="pp_search_container" id="pp_search_container_<?php echo $id;?>" style=" <?php echo $style; ?> ">
        <div style="display:none" class="chrome_xp"></div>
		<form autocomplete="off" action="<?php echo get_permalink(get_option('woocommerce_search_page_id'));?>" method="get" class="fr_search_widget" id="fr_pp_search_widget_<?php echo $id;?>">
        	<?php
			if (get_option('permalink_structure') == '') {
			?>
            <input type="hidden" name="page_id" value="<?php echo get_option('woocommerce_search_page_id'); ?>"  />
            <?php
			}
			
			if ($cat_slug != '') { ?>
            	<input type="hidden" name="scat" value="<?php echo $cat_slug; ?>"  />
            <?php
			} elseif ($tag_slug != '') { ?>
            	<input type="hidden" name="stag" value="<?php echo $tag_slug; ?>"  />
            <?php
			}
			?>
   			<div class="ctr_search">
			<input type="text" id="pp_course_<?php echo $id;?>" value="" name="rs" class="txt_livesearch" /><span class="bt_search" id="bt_pp_search_<?php echo $id;?>"></span>
            </div>
		</form>
        </div>
        <?php if (trim($style) == '') { ?>
        <div style="clear:both;"></div>
		<?php } ?>
    	<?php
		
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number_items'] = 6;
		$instance['text_lenght'] = 100;
		$instance['search_global'] = 1;
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'number_items' => 6, 'text_lenght' => 100, 'search_global' => 0) );
		$title = strip_tags($instance['title']);
		$number_items = strip_tags($instance['number_items']);
		$text_lenght = strip_tags($instance['text_lenght']);
		$search_global = $instance['search_global'];
?>
		<style>
			#woo_predictive_upgrade_area { border:2px solid #FF0;-webkit-border-radius:10px;-moz-border-radius:10px;-o-border-radius:10px; border-radius: 10px; padding:5px; position:relative}
			#woo_predictive_upgrade_area legend {margin-left:10px; font-weight:bold;}
		</style>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'woops'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
            <fieldset id="woo_predictive_upgrade_area"><legend><?php _e('Upgrade to Pro to activate', 'wpscps'); ?></legend>
            <p><label for="<?php echo $this->get_field_id('number_items'); ?>"><?php _e('Number of results to show:', 'woops'); ?></label> <input disabled="disabled" class="widefat" id="<?php echo $this->get_field_id('number_items'); ?>" name="<?php echo $this->get_field_name('number_items'); ?>" type="text" value="<?php echo esc_attr($number_items); ?>" /></p>
            <p><label for="<?php echo $this->get_field_id('text_lenght'); ?>"><?php _e(' Results description character count:', 'woops'); ?></label> <input disabled="disabled" class="widefat" id="<?php echo $this->get_field_id('text_lenght'); ?>" name="<?php echo $this->get_field_name('text_lenght'); ?>" type="text" value="<?php echo esc_attr($text_lenght); ?>" /></p>
            <p><input disabled="disabled" type="checkbox" id="<?php echo $this->get_field_id('search_global'); ?>" name="<?php echo $this->get_field_name('search_global'); ?>" value="1" checked="checked"  /> <label for="<?php echo $this->get_field_id('search_global'); ?>"><?php _e('Search all products.', 'woops'); ?></label></p>
            </fieldset>

<?php
	}
}
?>
