<?php
/**
 * WooCommerce Predictive Search Widget
 *
 * Table Of Contents
 *
 * get_items_search()
 * __construct()
 * widget()
 * woops_results_search_form()
 * update()
 * form()
 */
class WC_Predictive_Search_Widgets extends WP_Widget 
{
	
	public static function get_items_search() {
		$items_search = array(
				'product'				=> array( 'number' => 6, 'name' => wc_ps_ict_t__( 'Product Name', __('Product Name', 'woops') ) ),
				'post'					=> array( 'number' => 0, 'name' => wc_ps_ict_t__( 'Posts', __('Posts', 'woops') ) ),
				'page'					=> array( 'number' => 0, 'name' => wc_ps_ict_t__( 'Pages', __('Pages', 'woops') ) )
			);
			
		return $items_search;
	}

	function __construct() {
		$widget_ops = array('classname' => 'widget_products_predictive_search', 'description' => __( "User sees search results as they type in a dropdown - links through to 'All Search Results Page' that features endless scroll.", 'woops') );
		parent::__construct('products_predictive_search', __('WooCommerce Predictive Search', 'woops'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
		$number_items = $instance['number_items'];
		if (!is_array($number_items) || count($number_items) < 1 ) $number_items = array();
		if(!isset($instance['text_lenght']) || $instance['text_lenght'] < 0) $text_lenght = 100; 
		else $text_lenght = $instance['text_lenght'];
		$search_global = empty($instance['search_global']) ? 0 : $instance['search_global'];
		$show_price = empty($instance['show_price']) ? 0 : $instance['show_price'];
		
		if ( class_exists('SitePress') ) {
			$current_lang = ICL_LANGUAGE_CODE;
			$search_box_texts = ( isset($instance['search_box_text']) ? $instance['search_box_text'] : array() );
			if ( !is_array($search_box_texts) ) $search_box_texts = get_option('woocommerce_search_box_text', array() );
			if ( is_array($search_box_texts) && isset($search_box_texts[$current_lang]) ) $search_box_text = esc_attr( stripslashes( trim( $search_box_texts[$current_lang] ) ) );
			else $search_box_text = '';
		} else {
			$search_box_text = ( isset($instance['search_box_text']) ? $instance['search_box_text'] : '' );
			if ( is_array($search_box_text) || trim($search_box_text) == '' ) $search_box_text = get_option('woocommerce_search_box_text', '' );
			if ( is_array($search_box_text) ) $search_box_text = '';
		}

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		echo $this->woops_results_search_form($widget_id, $number_items, $text_lenght, '',$search_global, $search_box_text, $show_price );
		echo $after_widget;
	}
	
	public static function woops_results_search_form($widget_id, $number_items=array(), $text_lenght=100, $style='', $search_global = 0, $search_box_text = '', $show_price = 1 ) {
		
		global $woocommerce_search_page_id;
		
		$id = str_replace('products_predictive_search-','',$widget_id);

		$cat_in = 'all';

		$row = 0;
		if (!is_array($number_items) || count($number_items) < 1 || array_sum($number_items) < 1) {
			$items_search_default = WC_Predictive_Search_Widgets::get_items_search();
			$number_items_default = array();
			foreach ($items_search_default as $key => $data) {
				if ($data['number'] > 0) {
					$number_items_default[$key] = $data['number'];
				}
			}
			$number_items = $number_items_default;
		}
		
		$common = '';
		$search_list = array();
		foreach ($number_items as $key => $number) {
			if ($number > 0) {
				$row += $number;
				$row++;
				$search_list[] = $key;
			}
		}
		$search_in = json_encode($number_items);
		
		ob_start();
		?>
        <div class="pp_search_container" id="pp_search_container_<?php echo $id;?>" style=" <?php echo $style; ?> ">
        <div style="display:none" class="chrome_xp"></div>
		<form data-ps-id="<?php echo $id;?>" autocomplete="off" action="<?php echo str_replace(array('http:','https:'), '', get_permalink( $woocommerce_search_page_id ) ); ?>" method="get" class="fr_search_widget" id="fr_pp_search_widget_<?php echo $id;?>">
        	<?php
			if (get_option('permalink_structure') == '') {
			?>
            <input type="hidden" name="page_id" value="<?php echo $woocommerce_search_page_id; ?>"  />
             <?php if ( class_exists('SitePress') ) { ?>
            <input type="hidden" name="lang" value="<?php echo ICL_LANGUAGE_CODE; ?>"  />
			<?php } ?>
            <?php } ?>
   			<div class="ctr_search">
			<input type="text" id="pp_course_<?php echo $id; ?>" onblur="if (this.value == '') {this.value = '<?php echo esc_js( $search_box_text ); ?>';}" onfocus="if (this.value == '<?php echo esc_js( $search_box_text ); ?>') {this.value = '';}" value="<?php echo esc_attr( $search_box_text ); ?>" name="rs" class="txt_livesearch predictive_search_input" 
            data-ps-id="<?php echo $id; ?>"
            data-ps-default_text="<?php echo esc_attr( $search_box_text ); ?>" 
            data-ps-row="<?php echo esc_attr( $row ); ?>" 
            data-ps-text_lenght="<?php echo esc_attr( $text_lenght ); ?>" 
            data-ps-cat_in="<?php echo esc_attr( $cat_in ); ?>"
            <?php if ( $search_in != '' ) { ?>data-ps-popup_search_in="<?php echo esc_attr( $search_in ); ?>" <?php } ?>
            <?php if ( count( $search_list ) > 0 ) { ?>data-ps-search_in="<?php echo esc_attr( $search_list[0] ); ?>" <?php } ?>
            <?php if ( count( $search_list ) > 0 ) { ?>data-ps-search_other="<?php echo esc_attr( implode(",", $search_list) ); ?>" <?php } ?>
            data-ps-show_price="<?php echo $show_price; ?>" 
            />
            <span data-ps-id="<?php echo $id;?>" class="bt_search predictive_search_bt" id="bt_pp_search_<?php echo $id;?>"></span>
            </div>
            <input type="hidden" name="search_in" value="<?php echo $search_list[0]; ?>"  />
            <input type="hidden" name="cat_in" value="<?php echo esc_attr( $cat_in ); ?>"  />
            <input type="hidden" name="search_other" value="<?php echo implode(",", $search_list); ?>"  />
		</form>
        </div>
        <?php if (trim($style) == '') { ?>
        <div style="clear:both;"></div>
		<?php } ?>
    	<?php
		$search_form = ob_get_clean();
		return $search_form;
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number_items'] = $new_instance['number_items'];
		$instance['text_lenght'] = strip_tags($new_instance['text_lenght']);
		$instance['show_price'] = $new_instance['show_price'];
		$instance['search_global'] = 0;
		$instance['search_box_text'] = $new_instance['search_box_text'];
		return $instance;
	}

	function form( $instance ) {
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script("jquery-ui-sortable");
		wp_enqueue_script("jquery-ui-draggable");
		
		$global_search_box_text = get_option('woocommerce_search_box_text');
		$items_search_default = WC_Predictive_Search_Widgets::get_items_search();
		$number_items_default = array();
		foreach ($items_search_default as $key => $data) {
			$number_items_default[$key] = $data['number'];
		}
		unset($key);
		unset($data);
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'number_items' => $number_items_default, 'text_lenght' => 100, 'show_price' => 1, 'search_global' => 0, 'search_box_text' => $global_search_box_text) );
		$title = strip_tags($instance['title']);
		$number_items = $instance['number_items'];
		if (!is_array($number_items) || count($number_items) < count($items_search_default) ) $number_items = $number_items_default;
		$text_lenght = strip_tags($instance['text_lenght']);
		$show_price = $instance['show_price'];
		$search_box_text = $instance['search_box_text'];
?>
		<style type="text/css">
		.item_heading{ width:130px; display:inline-block;}
		ul.predictive_search_item li{padding-left:15px; background:url(<?php echo WOOPS_IMAGES_URL; ?>/sortable.gif) no-repeat left center; cursor:pointer;}
		ul.predictive_search_item li.ui-sortable-placeholder{border:1px dotted #111; visibility:visible !important; background:none;}
		ul.predictive_search_item li.ui-sortable-helper{background-color:#DDD;}
		</style>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'woops'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<?php
		if ( class_exists('SitePress') ) {
			if ( !is_array($search_box_text) ) $search_box_text = array();
			global $sitepress;
			$active_languages = $sitepress->get_active_languages();
			if ( is_array($active_languages)  && count($active_languages) > 0 ) {
				foreach ( $active_languages as $language ) {
		?>
        	<p><label for="<?php echo $this->get_field_id('search_box_text'); ?>_<?php echo $language['code']; ?>"><?php _e('Search box text message', 'woops'); ?> (<?php echo $language['display_name']; ?>)</label> <input class="widefat" id="<?php echo $this->get_field_id('search_box_text'); ?>_<?php echo $language['code']; ?>" name="<?php echo $this->get_field_name('search_box_text'); ?>[<?php echo $language['code']; ?>]" type="text" value="<?php if ( isset( $search_box_text[$language['code'] ] ) ) esc_attr_e( $search_box_text[$language['code']] ); ?>" /></p>
        <?php
				}
			}
		} else {
			if ( is_array($search_box_text) ) $search_box_text = '';
		?>
            <p><label for="<?php echo $this->get_field_id('search_box_text'); ?>"><?php _e('Search box text message:', 'woops'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('search_box_text'); ?>" name="<?php echo $this->get_field_name('search_box_text'); ?>" type="text" value="<?php echo esc_attr($search_box_text); ?>" /></p>
		<?php } ?>
            <p><?php _e("Activate search 'types' for this widget by entering the number of results to show in the widget dropdown. &lt;empty&gt; = not activated. Sort order by drag and drop", 'woops'); ?></p>
            <ul class="ui-sortable predictive_search_item">
            <?php foreach ($number_items as $key => $value) { ?>
            	<?php if ( isset( $items_search_default[$key] ) ) { ?>
            	<li><span class="item_heading"><label for="search_<?php echo $key; ?>"><?php echo $items_search_default[$key]['name']; ?></label></span> <input id="search_<?php echo $key; ?>" name="<?php echo $this->get_field_name('number_items'); ?>[<?php echo $key; ?>]" type="text" value="<?php echo esc_attr($value); ?>" style="width:50px;" /></li>
            	<?php } ?>
            <?php } ?>
            </ul>
            <p><label><input type="checkbox" name="<?php echo $this->get_field_name('show_price'); ?>" value="1" <?php checked( $show_price, 1 ); ?>  /> <?php _e('Show Product prices', 'woops'); ?></label>
            </p>
            <p><label for="<?php echo $this->get_field_id('text_lenght'); ?>"><?php _e(' Results description character count:', 'woops'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('text_lenght'); ?>" name="<?php echo $this->get_field_name('text_lenght'); ?>" type="text" value="<?php echo esc_attr($text_lenght); ?>" /></p>
		<script>
		jQuery(document).ready(function() {
        	jQuery(".predictive_search_item").sortable();
		});
        </script>
<?php
	}
}
?>