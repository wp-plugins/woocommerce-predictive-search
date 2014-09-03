<?php
/**
 * WC Predictive Search Hook Filter
 *
 * Hook anf Filter into woocommerce plugin
 *
 * Table Of Contents
 *
 * add_search_widget_icon()
 * add_search_widget_mce_popup()
 * parse_shortcode_search_result()
 * get_product_price()
 * get_product_price_dropdown()
 * get_product_addtocart()
 * get_product_categories()
 * get_product_tags()
 * display_search()
 * get_result_search_page()
 */
class WC_Predictive_Search_Shortcodes
{
	
	public static function add_search_widget_icon($context){
		$image_btn = WOOPS_IMAGES_URL . "/ps_icon.png";
		$out = '<a href="#TB_inline?width=670&height=500&modal=false&inlineId=woo_search_widget_shortcode" class="thickbox" title="'.__('Insert WooCommerce Predictive Search Shortcode', 'woops').'"><img class="search_widget_shortcode_icon" src="'.$image_btn.'" alt="'.__('Insert WooCommerce Predictive Search Shortcode', 'woops').'" /></a>';
		return $context . $out;
	}
	
	//Action target that displays the popup to insert a form to a post/page
	public static function add_search_widget_mce_popup(){
		$items_search_default = WC_Predictive_Search_Widgets::get_items_search();
		?>
		<style type="text/css">
		#TB_ajaxContent{width:auto !important;}
		#TB_ajaxContent p {
			padding:2px 0;	
			margin:6px 0;
		}
		.field_content {
			padding:0 0 0 40px;
		}
		.field_content label{
			width:150px;
			float:left;
			text-align:left;
		}
		.a3-view-docs-button {
			background-color: #FFFFE0 !important;
			border: 1px solid #E6DB55 !important;
			border-radius: 3px;
			-webkit-border-radius: 3px;
			-moz-border-radius: 3px;
			color: #21759B !important;
			outline: 0 none;
			text-shadow:none !important;
			font-weight:normal !important;
			font-family: sans-serif;
			font-size: 12px;
			text-decoration: none;
			padding: 3px 8px;
			position: relative;
			margin-left: 4px;
			white-space:nowrap;
		}
		.a3-view-docs-button:hover {
			color: #D54E21 !important;
		}
		@media screen and ( max-width: 782px ) {
			#woo_search_box_text {
				width:100% !important;	
			}
		}
		@media screen and ( max-width: 480px ) {
			.a3_woocommerce_search_exclude_item {
				float:none !important;
				display:block;
			}
		}
		#woo_predictive_upgrade_area { border:2px solid #E6DB55;-webkit-border-radius:10px;-moz-border-radius:10px;-o-border-radius:10px; border-radius: 10px; padding:0; position:relative}
	  	#woo_predictive_upgrade_area h3{ margin-left:10px;}
		.a3-rev-logo-extensions { position:absolute; left:10px; top:0px; z-index:10; color:#46719D; }
		.a3-rev-logo-extensions:before {
		  font-family: "a3-sidebar-menu" !important;
		  font-style: normal !important;
		  font-weight: normal !important;
		  font-variant: normal !important;
		  text-transform: none !important;
		  speak: none;
		  line-height: 1;
		  -webkit-font-smoothing: antialiased;
		  -moz-osx-font-smoothing: grayscale;
			display:inline-block;
			font-size:25px !important;
			font-weight:400;
			height: 36px;
			padding: 8px 0;
			transition: all 0.1s ease-in-out 0s;
		  
		  content: "\a3" !important;
		}
	   	#woo_predictive_extensions { background:#FFFBCC; -webkit-border-radius:10px 10px 0 0;-moz-border-radius:10px 10px 0 0;-o-border-radius:10px 10px 0 0; border-radius: 10px 10px 0 0; color: #555555; margin: 0px; padding: 4px 8px 4px 40px; text-shadow: 0 1px 0 rgba(255, 255, 255, 0.8); position:relative;}
		</style>
		<div id="woo_search_widget_shortcode" style="display:none;">
		  <div>
			<h3><?php _e('Customize the Predictive Search Shortcode', 'woops'); ?> <a class="add-new-h2 a3-view-docs-button" target="_blank" href="<?php echo WOO_PREDICTIVE_SEARCH_DOCS_URI; ?>#section-16" ><?php _e('View Docs', 'woops'); ?></a></h3>
			<div style="clear:both"></div>
            <div id="woo_predictive_upgrade_area"><div class="a3-rev-logo-extensions"></div><?php echo WC_Predictive_Search::predictive_extension_shortcode(); ?>
			<div class="field_content">
            	<?php foreach ($items_search_default as $key => $data) { ?>
                <p><label for="woo_search_<?php echo $key ?>_items"><?php echo $data['name']; ?>:</label> <input disabled="disabled" style="width:100px;" size="10" id="woo_search_<?php echo $key ?>_items" name="woo_search_<?php echo $key ?>_items" type="text" value="<?php echo $data['number'] ?>" /> <span class="description"><?php _e('Number of', 'woops'); echo ' '.$data['name'].' '; _e('results to show in dropdown', 'woops'); ?></span></p> 
                <?php } ?>
                <p><label for="woo_search_show_price"><?php _e('Price', 'woops'); ?>:</label> <input disabled="disabled" type="checkbox" checked="checked" id="woo_search_show_price" name="woo_search_show_price" value="1" /> <span class="description"><?php _e('Show Product prices', 'woops'); ?></span></p>
            	<p><label for="woo_search_text_lenght"><?php _e('Characters', 'woops'); ?>:</label> <input disabled="disabled" style="width:100px;" size="10" id="woo_search_text_lenght" name="woo_search_text_lenght" type="text" value="100" /> <span class="description"><?php _e('Number of product description characters', 'woops'); ?></span></p>
                <p><label for="woo_search_align"><?php _e('Alignment', 'woops'); ?>:</label> <select disabled="disabled" style="width:100px" id="woo_search_align" name="woo_search_align"><option value="none" selected="selected"><?php _e('None', 'woops'); ?></option><option value="left-wrap"><?php _e('Left - wrap', 'woops'); ?></option><option value="left"><?php _e('Left - no wrap', 'woops'); ?></option><option value="center"><?php _e('Center', 'woops'); ?></option><option value="right-wrap"><?php _e('Right - wrap', 'woops'); ?></option><option value="right"><?php _e('Right - no wrap', 'woops'); ?></option></select> <span class="description"><?php _e('Horizontal aliginment of search box', 'woops'); ?></span></p>
                <p><label for="woo_search_width"><?php _e('Search box width', 'woops'); ?>:</label> <input disabled="disabled" style="width:100px;" size="10" id="woo_search_width" name="woo_search_width" type="text" value="200" />px</p>
                <p><label for="woo_search_box_text"><?php _e('Search box text message', 'woops'); ?>:</label> <input disabled="disabled" style="width:300px;" size="10" id="woo_search_box_text" name="woo_search_box_text" type="text" value="<?php echo get_option('woocommerce_search_box_text'); ?>" /></p>
                <p><label for="woo_search_padding"><strong><?php _e('Padding', 'woops'); ?></strong>:</label><br /> 
				<label for="woo_search_padding_top" style="width:auto; float:none"><?php _e('Above', 'woops'); ?>:</label><input disabled="disabled" style="width:50px;" size="10" id="woo_search_padding_top" name="woo_search_padding_top" type="text" value="10" />px &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <label for="woo_search_padding_bottom" style="width:auto; float:none"><?php _e('Below', 'woops'); ?>:</label> <input disabled="disabled" style="width:50px;" size="10" id="woo_search_padding_bottom" name="woo_search_padding_bottom" type="text" value="10" />px &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <label for="woo_search_padding_left" style="width:auto; float:none"><?php _e('Left', 'woops'); ?>:</label> <input disabled="disabled" style="width:50px;" size="10" id="woo_search_padding_left" name="woo_search_padding_left" type="text" value="0" />px &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <label for="woo_search_padding_right" style="width:auto; float:none"><?php _e('Right', 'woops'); ?>:</label> <input disabled="disabled" style="width:50px;" size="10" id="woo_search_padding_right" name="woo_search_padding_right" type="text" value="0" />px
                </p>
			</div>
            <p>&nbsp;&nbsp;<input disabled="disabled" type="button" class="button-primary" value="<?php _e('Insert Shortcode', 'woops'); ?>" />&nbsp;&nbsp;&nbsp;
            <a class="button" style="" href="#" onclick="tb_remove(); return false;"><?php _e('Cancel', 'woops'); ?></a>
			</p>
            <div style="clear:both;"></div>
           	</div>
		  </div>
          <div style="clear:both;"></div>
		</div>
<?php
	}
	
	public static function parse_shortcode_search_result($attributes) {
    	return WC_Predictive_Search_Shortcodes::display_search();	
    }
	
	public static function get_product_price($product_id, $show_price=true) {
		$product_price_output = '';
		if ($show_price) {
			$current_db_version = get_option( 'woocommerce_db_version', null );
			if ( version_compare( $current_db_version, '2.0', '<' ) && null !== $current_db_version ) {
				$current_product = new WC_Product($product_id);
			} elseif ( version_compare( WC()->version, '2.2.0', '<' ) ) {
				$current_product = get_product( $product_id );
			} else {
				$current_product = wc_get_product( $product_id );
			}
			if ($current_product->is_type('grouped')) {
				$product_price_output = '<div class="rs_rs_price">'.__('Price', 'woops').': '. $current_product->get_price_html(). '</div>';
			} elseif ($current_product->is_type('variable')) {
				$product_price_output = '<div class="rs_rs_price">'.__('Price', 'woops').': '. $current_product->get_price_html(). '</div>';
			} else {
				$product_price_output = '<div class="rs_rs_price">'.__('Price', 'woops').': '. $current_product->get_price_html(). '</div>';
			}
		}
		
		return $product_price_output;
	}
	
	public static function get_product_price_dropdown($product_id) {
		$product_price_output = '';
		$current_db_version = get_option( 'woocommerce_db_version', null );
		if ( version_compare( $current_db_version, '2.0', '<' ) && null !== $current_db_version ) {
			$current_product = new WC_Product($product_id);
		} elseif ( version_compare( WC()->version, '2.2.0', '<' ) ) {
			$current_product = get_product( $product_id );
		} else {
			$current_product = wc_get_product( $product_id );
		}
		if ($current_product->is_type('grouped')) {
			$product_price_output = '<span class="rs_price">'.__('Price', 'woops').': '. $current_product->get_price_html(). '</span>';
		} elseif ($current_product->is_type('variable')) {
			$product_price_output = '<span class="rs_price">'.__('Price', 'woops').': '. $current_product->get_price_html(). '</span>';
		} else {
			$product_price_output = '<span class="rs_price">'.__('Price', 'woops').': '. $current_product->get_price_html(). '</span>';
		}
		
		return $product_price_output;
	}
	
	public static function get_product_addtocart($product_id, $show_addtocart=true) {
		$product_addtocart_output = '';
		global $product;
		if ($show_addtocart) {
			$current_db_version = get_option( 'woocommerce_db_version', null );
			if ( version_compare( $current_db_version, '2.0', '<' ) && null !== $current_db_version ) {
				$current_product = new WC_Product($product_id);
			} elseif ( version_compare( WC()->version, '2.2.0', '<' ) ) {
				$current_product = get_product( $product_id );
			} else {
				$current_product = wc_get_product( $product_id );
			}
			$product = $current_product;
			ob_start();
			if (function_exists('woocommerce_template_loop_add_to_cart') )
				woocommerce_template_loop_add_to_cart();
			$product_addtocart_html = ob_get_clean();
			$product_addtocart_output = '<div class="rs_rs_addtocart">'. $product_addtocart_html. '</div>';
		}
		
		return $product_addtocart_output;
	}
	
	public static function get_product_categories($product_id, $show_categories=true) {
		$product_cats_output = '';
		if ($show_categories) {
			
			$product_cats = get_the_terms( $product_id, 'product_cat' );
						
			if ( $product_cats && ! is_wp_error( $product_cats ) ) {
				$product_cat_links = array();
				foreach ( $product_cats as $product_cat ) {
					$product_cat_links[] = '<a href="' .get_term_link($product_cat->slug, 'product_cat') .'">'.$product_cat->name.'</a>';
				}
				if (count($product_cat_links) > 0)
					$product_cats_output = '<div class="rs_rs_cat posted_in">'.__('Category', 'woops').': '.join( ", ", $product_cat_links ).'</div>';
			}
		}
		
		return $product_cats_output;
	}
	
	public static function get_product_tags($product_id, $show_tags=true) {
		$product_tags_output = '';
		if ($show_tags) {
			$product_tags = get_the_terms( $product_id, 'product_tag' );
						
			if ( $product_tags && ! is_wp_error( $product_tags ) ) {
				$product_tag_links = array();
				foreach ( $product_tags as $product_tag ) {
					$product_tag_links[] = '<a href="' .get_term_link($product_tag->slug, 'product_tag') .'">'.$product_tag->name.'</a>';
				}
				if (count($product_tag_links) > 0)
					$product_tags_output = '<div class="rs_rs_tag tagged_as">'.__('Tags', 'woops').': '.join( ", ", $product_tag_links ).'</div>';
			}
		}
		
		return $product_tags_output;
	}
	
	public static function display_search() {
		global $wp_query;
		global $wpdb;
		global $wc_predictive_id_excludes;
		$p = 0;
		$row = 10;
		$search_keyword = '';
		$cat_slug = '';
		$tag_slug = '';
		$extra_parameter = '';
		$show_price = false;
		$show_categories = false;
		$show_tags = false;
		$extra_parameter_admin = '';
		
		if (isset($wp_query->query_vars['keyword'])) $search_keyword = stripslashes( strip_tags( urldecode( $wp_query->query_vars['keyword'] ) ) );
		else if (isset($_REQUEST['rs']) && trim($_REQUEST['rs']) != '') $search_keyword = stripslashes( strip_tags( $_REQUEST['rs'] ) );
		
		$start = $p * $row;
		$end_row = $row;
				
		if ($search_keyword != '') {
			$args = array( 's' => $search_keyword, 'numberposts' => $row+1, 'offset'=> $start, 'orderby' => 'predictive', 'order' => 'ASC', 'post_type' => 'product', 'post_status' => 'publish', 'exclude' => $wc_predictive_id_excludes['exclude_products'], 'suppress_filters' => FALSE);
			if ($cat_slug != '') {
				$args['tax_query'] = array( array('taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => $cat_slug) );
				$extra_parameter_admin .= '&scat='.$cat_slug;
				if (get_option('permalink_structure') == '') 
					$extra_parameter .= '&scat='.$cat_slug;
				else
					$extra_parameter .= '/scat/'.$cat_slug;
			} elseif($tag_slug != '') {
				$args['tax_query'] = array( array('taxonomy' => 'product_tag', 'field' => 'slug', 'terms' => $tag_slug) );
				$extra_parameter_admin .= '&stag='.$tag_slug;
				if (get_option('permalink_structure') == '') 
					$extra_parameter .= '&stag='.$tag_slug;
				else
					$extra_parameter .= '/stag/'.$tag_slug;
			}
			
			$total_args = $args;
			$total_args['numberposts'] = -1;
			$total_args['offset'] = 0;
			
			//$search_all_products = get_posts($total_args);
									
			$search_products = get_posts($args);
			
			$html = '<div class="woocommerce">';			
			$html .= '<p class="rs_result_heading">'.__('Showing all results for your search', 'woops').' | '.$search_keyword.'</p>';
			if ( $search_products && count($search_products) > 0 ){
					
				$html .= '<style type="text/css">
				.rs_result_heading{margin:15px 0;}
				.ajax-wait{display: none; position: absolute; width: 100%; height: 100%; top: 0px; left: 0px; background:url("'.WOOPS_IMAGES_URL.'/ajax-loader.gif") no-repeat center center #EDEFF4; opacity: 1;text-align:center;}
				.ajax-wait img{margin-top:14px;}
				.p_data,.r_data,.q_data{display:none;}
				.rs_date{color:#777;font-size:small;}
				.rs_result_row{width:100%;float:left;margin:0px 0 10px;padding :0px 0 10px; 6px;border-bottom:1px solid #c2c2c2;}
				.rs_result_row:hover{opacity:1;}
				.rs_rs_avatar{width:64px;margin-right:10px;overflow: hidden;float:left; text-align:center;}
				.rs_rs_avatar img{width:100%;height:auto; padding:0 !important; margin:0 !important; border: none !important;}
				.rs_rs_name{margin-left:0px;}
				.rs_content{margin-left:74px;}
				.rs_more_result{display:none;width:240px;text-align:center;position:fixed;bottom:50%;left:50%;margin-left:-125px;background-color: black;opacity: .75;color: white;padding: 10px;border-radius:10px;-webkit-border-radius: 10px;-moz-border-radius: 10px}
				.rs_rs_price .oldprice{text-decoration:line-through; font-size:80%;}
				</style>';
				$html .= '<div class="rs_ajax_search_content">';
				$text_lenght = get_option('woocommerce_search_text_lenght');
				foreach ( $search_products as $product ) {
					$link_detail = get_permalink($product->ID);
					
					$avatar = WC_Predictive_Search::woops_get_product_thumbnail($product->ID,'shop_catalog',64,64);
					
					$product_price_output = WC_Predictive_Search_Shortcodes::get_product_price($product->ID, $show_price);
						
					$product_cats_output = WC_Predictive_Search_Shortcodes::get_product_categories($product->ID, $show_categories);
					
					$product_tags_output = WC_Predictive_Search_Shortcodes::get_product_tags($product->ID, $show_tags);
					
					$product_description = WC_Predictive_Search::woops_limit_words( strip_tags( WC_Predictive_Search::strip_shortcodes( strip_shortcodes( $product->post_content ) ) ),$text_lenght,'...');
					if (trim($product_description) == '') $product_description = WC_Predictive_Search::woops_limit_words( strip_tags( WC_Predictive_Search::strip_shortcodes( strip_shortcodes( $product->post_excerpt ) ) ),$text_lenght,'...');
					
					$html .= '<div class="rs_result_row"><span class="rs_rs_avatar">'.$avatar.'</span><div class="rs_content"><a href="'.$link_detail.'"><span class="rs_rs_name">'.stripslashes( $product->post_title).'</span></a>'.$product_price_output.'<div class="rs_rs_description">'.$product_description.'</div>'.$product_cats_output.$product_tags_output.'</div></div>';
					
					$html .= '<div style="clear:both"></div>';
					$end_row--;
					if ($end_row < 1) break;
				}
				$html .= '</div>';
				if ( count($search_products) > $row ) {
					$woops_get_result_search_page = wp_create_nonce("woops-get-result-search-page");
					
					$html .= '<div id="search_more_rs"></div><div style="clear:both"></div><div id="rs_more_check"></div><div class="rs_more_result"><span class="p_data">'.($p + 1).'</span><img src="'.WOOPS_IMAGES_URL.'/more-results-loader.gif" /><div><em>'.__('Loading More Results...', 'woops').'</em></div></div>';
					$html .= "<script>jQuery(document).ready(function() {
var search_rs_obj = jQuery('#rs_more_check');
var is_loading = false;

function auto_click_more() {
	if (is_loading == false) {
		var visibleAtTop = search_rs_obj.offset().top + search_rs_obj.height() >= jQuery(window).scrollTop();
		var visibleAtBottom = search_rs_obj.offset().top <= jQuery(window).scrollTop() + jQuery(window).height();
		if (visibleAtTop && visibleAtBottom) {
			is_loading = true;
			jQuery('.rs_more_result').fadeIn('normal');
			var p_data_obj = jQuery('.rs_more_result .p_data');
			var p_data = p_data_obj.html();
			p_data_obj.html('');
			var urls = '&p='+p_data+'&row=".$row."&q=".$search_keyword.$extra_parameter_admin."&action=woops_get_result_search_page&security=".$woops_get_result_search_page."';
			jQuery.post('".admin_url( 'admin-ajax.php', 'relative' )."', urls, function(theResponse){
				if(theResponse != ''){
					var num = parseInt(p_data)+1;
					p_data_obj.html(num);
					jQuery('#search_more_rs').append(theResponse);
					is_loading = false;
					jQuery('.rs_more_result').fadeOut('normal');
				}else{
					jQuery('.rs_more_result').html('<em>".__('No More Results to Show', 'woops')."</em>').fadeOut(2000);
				}
			});
			return false;
		}
	}
}
jQuery(window).scroll(function(){
	auto_click_more();
});
auto_click_more();						
});</script>";
				}
			} else {
				$html .= '<p style="text-align:center">'.__('Nothing Found! Please refine your search and try again.', 'woops').'</p>';
			}
			$html .= '</div>'; 
			
			return $html;
		}
	}
	
	public static function get_result_search_page() {
		check_ajax_referer( 'woops-get-result-search-page', 'security' );
		add_filter( 'posts_search', array('WC_Predictive_Search_Hook_Filter', 'search_by_title_only'), 500, 2 );
		add_filter( 'posts_orderby', array('WC_Predictive_Search_Hook_Filter', 'predictive_posts_orderby'), 500, 2 );
		add_filter( 'posts_request', array('WC_Predictive_Search_Hook_Filter', 'posts_request_unconflict_role_scoper_plugin'), 500, 2);
		global $wc_predictive_id_excludes;
		$p = 1;
		$row = 10;
		$search_keyword = '';
		$cat_slug = '';
		$tag_slug = '';
		$extra_parameter = '';
		$show_price = false;
		$show_categories = false;
		$show_tags = false;
		if (isset($_REQUEST['p']) && $_REQUEST['p'] > 0) $p = $_REQUEST['p'];
		if (isset($_REQUEST['row']) && $_REQUEST['row'] > 0) $row = $_REQUEST['row'];
		if (isset($_REQUEST['q']) && trim($_REQUEST['q']) != '') $search_keyword = $_REQUEST['q'];
		
		$start = $p * $row;
		$end = $start + $row;
		$end_row = $row;
		
		if ($search_keyword != '') {
			$args = array( 's' => $search_keyword, 'numberposts' => $row+1, 'offset'=> $start, 'orderby' => 'predictive', 'order' => 'ASC', 'post_type' => 'product', 'post_status' => 'publish', 'exclude' => $wc_predictive_id_excludes['exclude_products'], 'suppress_filters' => FALSE);
			if ($cat_slug != '') {
				$args['tax_query'] = array( array('taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => $cat_slug) );
				$extra_parameter .= '&scat='.$cat_slug;
			} elseif($tag_slug != '') {
				$args['tax_query'] = array( array('taxonomy' => 'product_tag', 'field' => 'slug', 'terms' => $tag_slug) );
				$extra_parameter .= '&stag='.$tag_slug;
			}
			
			$total_args = $args;
			$total_args['numberposts'] = -1;
			$total_args['offset'] = 0;
			
			//$search_all_products = get_posts($total_args);
									
			$search_products = get_posts($args);
						
			$html = '';
			if ( $search_products && count($search_products) > 0 ){
				$html .= '<div class="rs_ajax_search_content">';
				$text_lenght = get_option('woocommerce_search_text_lenght');
				foreach ( $search_products as $product ) {
					$link_detail = get_permalink($product->ID);
					
					$avatar = WC_Predictive_Search::woops_get_product_thumbnail($product->ID,'shop_catalog',64,64);
					
					$product_price_output = WC_Predictive_Search_Shortcodes::get_product_price($product->ID, $show_price);
						
					$product_cats_output = WC_Predictive_Search_Shortcodes::get_product_categories($product->ID, $show_categories);
					
					$product_tags_output = WC_Predictive_Search_Shortcodes::get_product_tags($product->ID, $show_tags);
					
					$product_description = WC_Predictive_Search::woops_limit_words( strip_tags( WC_Predictive_Search::strip_shortcodes( strip_shortcodes( $product->post_content ) ) ),$text_lenght,'...');
					if (trim($product_description) == '') $product_description = WC_Predictive_Search::woops_limit_words( strip_tags( WC_Predictive_Search::strip_shortcodes( strip_shortcodes( $product->post_excerpt ) ) ),$text_lenght,'...');
										
					$html .= '<div class="rs_result_row"><span class="rs_rs_avatar">'.$avatar.'</span><div class="rs_content"><a href="'.$link_detail.'"><span class="rs_rs_name">'.stripslashes( $product->post_title).'</span></a>'.$product_price_output.'<div class="rs_rs_description">'.$product_description.'</div>'.$product_cats_output.$product_tags_output.'</div></div>';
					$html .= '<div style="clear:both"></div>';
					$end_row--;
					if ($end_row < 1) break;
				}
				
				if ( count($search_products) <= $row ) {
					
					$html .= '';
				}
				
				$html .= '</div>';
			}
			echo $html;
		}
		die();
	}
}
?>