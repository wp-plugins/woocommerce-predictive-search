<?php

/**
 * Register Activation Hook
 */
update_option('wc_predictive_search_plugin', 'woo_predictive_search');
function wc_predictive_install()
{
    WC_Predictive_Search::create_page('woocommerce-search', 'woocommerce_search_page_id', __('Woocommerce Predictive Search', 'woops'), '[woocommerce_search]');

    // Set Settings Default from Admin Init
    global $wc_predictive_search_admin_init;
    $wc_predictive_search_admin_init->set_default_settings();
    update_option('wc_predictive_search_lite_version', '2.2.8');
    flush_rewrite_rules();

    update_option('wc_predictive_search_just_installed', true);
}

function woops_init()
{
    if (get_option('wc_predictive_search_just_installed')) {
        delete_option('wc_predictive_search_just_installed');
        wp_redirect(admin_url('admin.php?page=woo-predictive-search', 'relative'));
        exit;
    }
    load_plugin_textdomain('woops', false, WOOPS_FOLDER . '/languages');
}

// Add language
add_action('init', 'woops_init');

// Add custom style to dashboard
add_action('admin_enqueue_scripts', array('WC_Predictive_Search_Hook_Filter', 'a3_wp_admin'));

add_action('plugins_loaded', array('WC_Predictive_Search_Hook_Filter', 'plugins_loaded'), 8);

// Add text on right of Visit the plugin on Plugin manager page
add_filter('plugin_row_meta', array('WC_Predictive_Search_Hook_Filter', 'plugin_extra_links'), 10, 2);

function register_widget_woops_predictive_search()
{
    register_widget('WC_Predictive_Search_Widgets');
}

// Need to call Admin Init to show Admin UI
global $wc_predictive_search_admin_init;
$wc_predictive_search_admin_init->init();

// Add upgrade notice to Dashboard pages
add_filter($wc_predictive_search_admin_init->plugin_name . '_plugin_extension', array('WC_Predictive_Search', 'plugin_extension'));

// Custom Rewrite Rules
add_action('init', array('WC_Predictive_Search_Hook_Filter', 'custom_rewrite_rule'), 101);

// Registry widget
add_action('widgets_init', 'register_widget_woops_predictive_search');

// Add shortcode [woocommerce_search]
add_shortcode('woocommerce_search', array('WC_Predictive_Search_Shortcodes', 'parse_shortcode_search_result'));

// Add Predictive Search Meta Box to all post type
add_action('add_meta_boxes', array('WC_Predictive_Search_Meta', 'create_custombox'), 9);

// Save Predictive Search Meta Box to all post type
if (in_array(basename($_SERVER['PHP_SELF']), array('post.php', 'page.php', 'page-new.php', 'post-new.php'))) {
    add_action('save_post', array('WC_Predictive_Search_Meta', 'save_custombox'));
}

// Add search widget icon to Page Editor
if (in_array(basename($_SERVER['PHP_SELF']), array('post.php', 'page.php', 'page-new.php', 'post-new.php'))) {
    add_action('media_buttons_context', array('WC_Predictive_Search_Shortcodes', 'add_search_widget_icon'));
    add_action('admin_footer', array('WC_Predictive_Search_Shortcodes', 'add_search_widget_mce_popup'));
}

if (!is_admin()) {
    add_filter('posts_search', array('WC_Predictive_Search_Hook_Filter', 'search_by_title_only'), 500, 2);
    add_filter('posts_orderby', array('WC_Predictive_Search_Hook_Filter', 'predictive_posts_orderby'), 500, 2);
    add_filter('posts_request', array('WC_Predictive_Search_Hook_Filter', 'posts_request_unconflict_role_scoper_plugin'), 500, 2);
}

// AJAX get result search page
add_action('wp_ajax_woops_get_result_search_page', array('WC_Predictive_Search_Shortcodes', 'get_result_search_page'));
add_action('wp_ajax_nopriv_woops_get_result_search_page', array('WC_Predictive_Search_Shortcodes', 'get_result_search_page'));

// AJAX get result search popup
add_action('wp_ajax_woops_get_result_popup', array('WC_Predictive_Search_Hook_Filter', 'get_result_popup'));
add_action('wp_ajax_nopriv_woops_get_result_popup', array('WC_Predictive_Search_Hook_Filter', 'get_result_popup'));

add_filter( 'pre_get_posts', array('WC_Predictive_Search_Hook_Filter', 'pre_get_posts'), 500 );

if (!is_admin()) add_action('init', array('WC_Predictive_Search_Hook_Filter', 'add_frontend_style'));

// Check upgrade functions
add_action('plugins_loaded', 'woo_ps_lite_upgrade_plugin');
function woo_ps_lite_upgrade_plugin()
{

    // Upgrade to 1.0.9
    if (version_compare(get_option('wc_predictive_search_lite_version'), '2.0') === -1) {
        WC_Predictive_Search::upgrade_version_2_0();
        update_option('wc_predictive_search_lite_version', '2.0');
    }

    update_option('wc_predictive_search_lite_version', '2.2.8');
}
?>