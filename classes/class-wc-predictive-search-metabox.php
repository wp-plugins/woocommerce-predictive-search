<?php
/**
 * Predictive Search Meta
 *
 * Class Function into WP e-Commerce plugin
 *
 * Table Of Contents
 *
 *
 * create_custombox()
 * a3_people_metabox()
 */
class WC_Predictive_Search_Meta
{
	public static function create_custombox() {
		global $post;
		$exclude_items = array();
		if (get_post_type($post->ID) == 'product') {
			$exclude_items = (array) get_option('woocommerce_search_exclude_products');
		}
		$check = '';
		if (is_array($exclude_items) && in_array($post->ID, $exclude_items)) {
			$check = 'checked="checked"';
		}
		
		if (get_post_type($post->ID) == 'product') {
			$hide_item_from_result_text = ' <span style="float:right;" class="a3_woocommerce_search_exclude_item"><label><input style="position: relative; top: 2px;" type="checkbox" '.$check.' value="1" name="_woocommerce_search_exclude_item" /> '.__('Hide from Predictive Search results.', 'woops').'</label></span>';
		} else {
			$hide_item_from_result_text = ' <span style="float:right;" class="a3_woocommerce_search_exclude_item"><label><input disabled="disabled" style="position: relative; top: 2px;" type="checkbox" checked="checked" value="1" name="_woocommerce_search_exclude_item" /> '.__('Hide from Predictive Search results.', 'woops').'</label></span>';
		}
		
		add_meta_box( 'wc_predictive_search_metabox', __('Predictive Search Meta', 'woops').$hide_item_from_result_text , array('WC_Predictive_Search_Meta','data_metabox'), 'post', 'normal', 'high' );
		
		add_meta_box( 'wc_predictive_search_metabox', __('Predictive Search Meta', 'woops').$hide_item_from_result_text , array('WC_Predictive_Search_Meta','data_metabox'), 'page', 'normal', 'high' );
		add_meta_box( 'wc_predictive_search_metabox', __('Predictive Search Meta', 'woops').$hide_item_from_result_text , array('WC_Predictive_Search_Meta','data_metabox'), 'product', 'normal', 'high' );
	}
	
	public static function data_metabox() {
		global $post;
		$postid = $post->ID;
				
	?>
    	<style>
			#woo_predictive_upgrade_area_box { border:2px solid #E6DB55;-webkit-border-radius:10px;-moz-border-radius:10px;-o-border-radius:10px; border-radius: 10px; padding:10px; position:relative}
			#woo_predictive_upgrade_area_box legend {margin-left:4px; font-weight:bold;}
		</style>
    	<fieldset id="woo_predictive_upgrade_area_box"><legend><?php _e('Upgrade to','woops'); ?> <a href="<?php echo WOOPS_AUTHOR_URI; ?>" target="_blank"><?php _e('Pro Version', 'woops'); ?></a> <?php _e('to activate', 'woops'); ?></legend>
    	<table class="form-table" cellspacing="0">
        	<tr valign="top">
				<th scope="rpw" class="titledesc"><label for="_predictive_search_focuskw"><?php _e('Focus Keywords', 'woops'); ?></label></th>
				<td class="forminp"><div class="wide_div"><input type="text" value="" id="_predictive_search_focuskw" name="_predictive_search_focuskw" style="width:98%;" /></div></td>
			</tr>
        </table>
        </fieldset>
	<?php
		
	}
	
	public static function save_custombox($post_id) {
		$post_status = get_post_status($post_id);
		$post_type = get_post_type($post_id);
		if(in_array($post_type, array('product') ) && isset($_REQUEST['_predictive_search_focuskw']) && $post_status != false  && $post_status != 'inherit') {
			extract($_REQUEST);
			
			$exclude_option = 'woocommerce_search_exclude_products';
			
			$exclude_items = (array) get_option($exclude_option);
			if (!is_array($exclude_items)) $exclude_items = array();
			
			if (isset($_REQUEST['_woocommerce_search_exclude_item']) && $_REQUEST['_woocommerce_search_exclude_item'] == 1) {
				if (!in_array($post_id, $exclude_items)) $exclude_items[] = $post_id;
			} else {
				$exclude_items = array_diff($exclude_items, array($post_id));
			}
			update_option($exclude_option, $exclude_items);
		}
	}
}
?>