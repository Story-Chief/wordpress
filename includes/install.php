<?php

namespace Storychief;

/**
 * Compatibility check on installation.
 * Bail if REST API not available or WP is outdated.
 */
function activate() {
    if (version_compare($GLOBALS['wp_version'], '4.6', '<')) {
        \Storychief\Admin::notice_invalid_version();
        \Storychief\Admin::admin_notice();
        bail_on_activate();
    } else if (!has_action('rest_api_init')) {
        \Storychief\Admin::notice_parent_plugin_required();
        \Storychief\Admin::admin_notice();
        bail_on_activate();
    }
}
register_activation_hook( STORYCHIEF_DIR . '/storychief.php', __NAMESPACE__.'\activate' );


/**
 * Bail on plugin activation
 * @param bool $deactivate
 */
function bail_on_activate($deactivate = true) {
    if ($deactivate) {
        $plugins = get_option('active_plugins');
        $storychief = plugin_basename(STORYCHIEF_DIR . '/storychief.php');
        $update = false;
        foreach ($plugins as $i => $plugin) {
            if ($plugin === $storychief) {
                $plugins[$i] = false;
                $update = true;
            }
        }

        if ($update) {
            update_option('active_plugins', array_filter($plugins));
        }
    }
    exit;
}

/**
 * Deactivate plugin
 */
function deactivate() {
    \Storychief\Settings\delete_sc_option('encryption_key');
    \Storychief\Settings\delete_sc_option('test_mode');
    \Storychief\Settings\delete_sc_option('author_create');
    \Storychief\Settings\delete_sc_option('category_create');
    \Storychief\Settings\delete_sc_option('tag_create');
    \Storychief\Settings\delete_sc_option('sideload_images');
    \Storychief\Settings\delete_sc_option('meta_fb_pages');
}
register_deactivation_hook( STORYCHIEF_DIR . '/storychief.php', __NAMESPACE__.'\deactivate' );
