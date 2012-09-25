<?php
/**
 * Register Activation Hook
 */
function wc_predictive_install(){
	WC_Predictive_Search::create_page( esc_sql( _x('woocommerce-search', 'page_slug', 'woops') ), 'woocommerce_search_page_id', __('Woocommerce Predictive Search', 'woops'), '[woocommerce_search]' );
	WC_Predictive_Search_Settings::set_setting();
}

function woops_init() {
	load_plugin_textdomain( 'woops', false, WOOPS_FOLDER.'/languages' );
}

// Add language
add_action('init', 'woops_init');

// Add text on right of Visit the plugin on Plugin manager page
add_filter( 'plugin_row_meta', array('WC_Predictive_Search_Hook_Filter', 'plugin_extra_links'), 10, 2 );

function register_widget_woops_predictive_search() {
	register_widget('WC_Predictive_Search_Widgets');
}

// Registry widget
add_action('widgets_init', 'register_widget_woops_predictive_search');

// Add shortcode [woocommerce_search]
add_shortcode('woocommerce_search', array('WC_Predictive_Search_Shortcodes', 'parse_shortcode_search_result'));

// Add search widget icon to Page Editor
if (in_array (basename($_SERVER['PHP_SELF']), array('post.php', 'page.php', 'page-new.php', 'post-new.php') ) ) {
	add_action('media_buttons_context', array('WC_Predictive_Search_Shortcodes', 'add_search_widget_icon') );
	add_action('admin_footer', array('WC_Predictive_Search_Shortcodes', 'add_search_widget_mce_popup'));
}

add_filter( 'posts_search', array('WC_Predictive_Search_Hook_Filter', 'search_by_title_only'), 500, 2 );

// AJAX get result search page
add_action('wp_ajax_woops_get_result_search_page', array('WC_Predictive_Search_Shortcodes', 'get_result_search_page'));
add_action('wp_ajax_nopriv_woops_get_result_search_page', array('WC_Predictive_Search_Shortcodes', 'get_result_search_page'));

// AJAX get result search popup
add_action('wp_ajax_woops_get_result_popup', array('WC_Predictive_Search', 'get_result_popup'));
add_action('wp_ajax_nopriv_woops_get_result_popup', array('WC_Predictive_Search', 'get_result_popup'));

global $wc_predictive;
$wc_predictive = new WC_Predictive_Search_Settings();
?>