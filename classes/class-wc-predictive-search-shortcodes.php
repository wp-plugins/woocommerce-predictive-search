<?php
/**
 * WC Predictive Search Hook Filter
 *
 * Hook anf Filter into woocommerce plugin
 *
 * Table Of Contents
 *
 * parse_shortcode_search_result()
 * display_search()
 */
class WC_Predictive_Search_Shortcodes 
{

	public static function parse_shortcode_search_result($attributes) {
		$search_results = '';
		global $woocommerce_search_page_id;
		global $wp_query;

		$search_keyword = '';
		if (isset($wp_query->query_vars['keyword'])) $search_keyword = stripslashes( strip_tags( urldecode( $wp_query->query_vars['keyword'] ) ) );
		else if (isset($_REQUEST['rs']) && trim($_REQUEST['rs']) != '') $search_keyword = stripslashes( strip_tags( $_REQUEST['rs'] ) );

		$search_results .= WC_Predictive_Search_Shortcodes::display_search();
    	return $search_results;	
    }
						
	public static function display_search() {
		global $wp_query;
		global $wpdb;
		global $woocommerce_search_page_id;
	
		$items_search_default = WC_Predictive_Search_Widgets::get_items_search();
		$search_keyword = '';
		$search_in = 'product';
		$search_other = '';
		$cat_in = 'all';
		
		if (isset($wp_query->query_vars['keyword'])) $search_keyword = stripslashes( strip_tags( urldecode( $wp_query->query_vars['keyword'] ) ) );
		else if (isset($_REQUEST['rs']) && trim($_REQUEST['rs']) != '') $search_keyword = stripslashes( strip_tags( $_REQUEST['rs'] ) );
		
		if (isset($wp_query->query_vars['cat-in'])) $cat_in = stripslashes( strip_tags( urldecode( $wp_query->query_vars['cat-in'] ) ) );
		else if (isset($_REQUEST['cat_in']) && trim($_REQUEST['cat_in']) != '') $cat_in = stripslashes( strip_tags( $_REQUEST['cat_in'] ) );
		
		if (isset($wp_query->query_vars['search-in'])) $search_in = stripslashes( strip_tags( urldecode( $wp_query->query_vars['search-in'] ) ) );
		else if (isset($_REQUEST['search_in']) && trim($_REQUEST['search_in']) != '') $search_in = stripslashes( strip_tags( $_REQUEST['search_in'] ) );
		
		if (isset($wp_query->query_vars['search-other'])) $search_other = stripslashes( strip_tags( urldecode( $wp_query->query_vars['search-other'] ) ) );
		else if (isset($_REQUEST['search_other']) && trim($_REQUEST['search_other']) != '') $search_other = stripslashes( strip_tags( $_REQUEST['search_other'] ) );
		
		$permalink_structure = get_option( 'permalink_structure' );

		if ( $search_keyword != '' && $search_in != '' ) {

			global $ps_search_list, $ps_current_search_in;

			ob_start();
		?>
		<div id="ps_results_container" class="woocommerce">
			<style type="text/css">
				.rs_result_heading{margin:15px 0;}
				.ajax-wait{display: none; position: absolute; width: 100%; height: 100%; top: 0px; left: 0px; background:url("<?php echo WOOPS_IMAGES_URL; ?>/ajax-loader.gif") no-repeat center center #EDEFF4; opacity: 1;text-align:center;}
				.ajax-wait img{margin-top:14px;}
				.p_data,.r_data,.q_data{display:none;}
				.rs_date{color:#777;font-size:small;}
				.rs_result_row{width:100%;float:left;margin:0px 0 10px;padding :0px 0 10px; 6px;border-bottom:1px solid #c2c2c2;}
				.rs_result_row:hover{opacity:1;}
				.rs_rs_avatar{width:64px;margin-right:10px;overflow: hidden;float:left; text-align:center;}
				.rs_rs_avatar img{width:100%;height:auto; padding:0 !important; margin:0 !important; border: none !important;}
				.rs_rs_name{margin-left:0px;}
				.rs_content{margin-left:74px;}
				.ps_more_result{display:none;width:240px;text-align:center;position:fixed;bottom:50%;left:50%;margin-left:-125px;background-color: black;opacity: .75;color: white;padding: 10px;border-radius:10px;-webkit-border-radius: 10px;-moz-border-radius: 10px}
				.rs_rs_price .oldprice{text-decoration:line-through; font-size:80%;}
				.rs_result_others { margin-bottom:20px; }
				.rs_result_others_heading {font-weight:bold;} 
				.ps_navigation_activated { font-weight:bold;}
			</style>
		

		<?php if ( count( $ps_search_list ) > 0 ) { ?>
			<p class="rs_result_heading"><?php wc_ps_ict_t_e( 'Viewing all', __('Viewing all', 'woops') ); ?> <strong><span class="ps_heading_search_in_name"><?php echo $items_search_default[$ps_current_search_in]['name']; ?></span></strong> <?php wc_ps_ict_t_e( 'Search Result Text', __('search results for your search query', 'woops') ); ?> <strong><?php echo $search_keyword; ?></strong></p>
		<?php } ?>
		<?php	
			if ( count( $ps_search_list ) > 1 ) {
				if ( $permalink_structure == '')
					$other_link_search = get_permalink( $woocommerce_search_page_id ).'&rs='. urlencode($search_keyword);
				else
					$other_link_search = rtrim( get_permalink( $woocommerce_search_page_id ), '/' ).'/keyword/'. urlencode($search_keyword);
				$line_vertical = '';
		?>
			<div class="rs_result_others"><div class="rs_result_others_heading"><?php wc_ps_ict_t_e( 'Sort Text', __('Sort Search Results by', 'woops') ); ?></div>
		<?php
				foreach ( $ps_search_list as $search_other_item ) {
					if ( ! isset( $items_search_default[$search_other_item] ) ) continue;

					if ( $permalink_structure == '' ) {
		?>
        		<?php echo $line_vertical; ?><span class="rs_result_other_item"><a class="ps_navigation ps_navigation<?php echo $search_other_item; ?>" href="<?php echo $other_link_search; ?>&search_in=<?php echo $search_other_item; ?>&cat_in=<?php echo $cat_in; ?>&search_other=<?php echo $search_other; ?>" data-href="?page_id=<?php echo $woocommerce_search_page_id; ?>&rs=<?php echo urlencode($search_keyword); ?>&search_in=<?php echo $search_other_item; ?>&cat_in=<?php echo $cat_in; ?>&search_other=<?php echo $search_other; ?>" alt=""><?php echo $items_search_default[$search_other_item]['name']; ?></a></span>
        <?php
					} else {
		?>
				<?php echo $line_vertical; ?><span class="rs_result_other_item"><a class="ps_navigation ps_navigation<?php echo $search_other_item; ?>" href="<?php echo $other_link_search; ?>/search-in/<?php echo $search_other_item; ?>/cat-in/<?php echo $cat_in; ?>/search-other/<?php echo $search_other; ?>" data-href="keyword/<?php echo urlencode($search_keyword); ?>/search-in/<?php echo $search_other_item; ?>/cat-in/<?php echo $cat_in; ?>/search-other/<?php echo $search_other; ?>" alt=""><?php echo $items_search_default[$search_other_item]['name']; ?></a></span>
		<?php
					}
					$line_vertical = ' | ';
				}
		?>
			</div>
		<?php
			}
		?>
        	<div id="ps_list_items_container">
            </div>
            <div style="clear:both"></div>
            <div class="ps_more_result" id="ps_more_result_popup">
                <img src="<?php echo WOOPS_IMAGES_URL; ?>/more-results-loader.gif" />
                <div><em><?php wc_ps_ict_t_e( 'Loading Text', __('Loading More Results...', 'woops') ); ?></em></div>
            </div>
            <div class="ps_more_result" id="ps_no_more_result_popup"><em><?php wc_ps_ict_t_e( 'No More Result Text', __('No More Results to Show', 'woops') ); ?></em></div>
            <div class="ps_more_result" id="ps_fetching_result_popup">
                <img src="<?php echo WOOPS_IMAGES_URL; ?>/more-results-loader.gif" />
                <div><em><?php wc_ps_ict_t_e( 'Fetching Text', __('Fetching search results...', 'woops') ); ?></em></div>
            </div>
            <div class="ps_more_result" id="ps_no_result_popup"><em><?php wc_ps_ict_t_e( 'No Fetching Result Text', __('No Results to Show', 'woops') ); ?></em></div>
            <div id="ps_footer_container">
            </div>
		</div>
        <script type="text/javascript">
		(function($) {
		$(function(){
			wc_ps_app.start();
		});
		})(jQuery);
		</script>
		<?php
			
			$output = ob_get_clean();
			
			return $output;
        }
	}	
}
?>