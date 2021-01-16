<?php

namespace Storychief\Compatibility;

/**
 * Don't let WP-SpamShield block the webhook.
 *
 * By default, WP-SpamShield's Anti-Spam for Miscellaneous Forms feature will block incoming POST
 * requests that aren't explicitly whitelisted.
 *
 * @link https://www.redsandmarketing.com/plugins/wp-spamshield-anti-spam/compatibility-guide/
 *
 * @param bool $bypass Whether or not to bypass WP-SpamShield for the request.
 *
 * @return bool The possibly-modified $bypass value.
 */
function wpspamshield_whitelist_webhook( $bypass ) {
    if ( untrailingslashit( $_SERVER['REQUEST_URI'] ) === '/' . rest_get_url_prefix() . '/storychief/webhook' ) {
        $bypass = true;
    }

    return $bypass;
}
add_filter( 'wpss_misc_form_spam_check_bypass', __NAMESPACE__ . '\wpspamshield_whitelist_webhook' );

/**
 * Check if Divi theme is active
 * @return bool
 */
function isDiviThemeActive() {
    $theme = wp_get_theme();
    return ('Divi' == $theme->name || 'Divi' == $theme->parent_theme);
}

/**
 * Check if Divi Builder is active
 * @return bool
 */
function isDiviBuilderActive() {
    $divi_options = get_option('et_divi');
    if(!isset($divi_options['et_pb_post_type_integration']['post'])) {
        $active_builder = true;
    } else {
        $active_builder = filter_var($divi_options['et_pb_post_type_integration']['post'], FILTER_VALIDATE_BOOLEAN);
    }
    return isDiviThemeActive() && $active_builder;
}

/**
 * Enable Divi Builder and save default settings
 *
 * @param array $story The reformatted StoryChief
 *
 * @return void
 */
function enable_divi_builder($story) {
    $post_id = $story['external_id'];

    $post_content_prefix = '[et_pb_section][et_pb_row][et_pb_column][et_pb_text]';
    $post_content_suffix = '[/et_pb_text][/et_pb_column][/et_pb_row][/et_pb_section]';
    $post_content = $story['content'];

    $updated_post = array(
        'ID' => $post_id,
        'post_content' => $post_content_prefix . $post_content . $post_content_suffix,
    );

    \Storychief\Webhook\safely_upsert_story($updated_post);

    update_post_meta($post_id, '_et_pb_old_content', $post_content);
    update_post_meta($post_id, '_et_pb_use_builder', 'on');
    update_post_meta($post_id, '_et_pb_show_page_creation', 'on');

    $dot_nav = \Storychief\Settings\get_sc_option('divi_dot_navigation');
    if($dot_nav){
        update_post_meta($post_id, '_et_pb_side_nav', $dot_nav);
    }

    $show_title = \Storychief\Settings\get_sc_option('divi_show_title');
    if($show_title){
        update_post_meta($post_id, '_et_pb_show_title', $show_title);
    }
}
if(isDiviBuilderActive()){
    add_action('storychief_after_publish_action', __NAMESPACE__ . '\enable_divi_builder', 2);
}

/**
 * Enable Divi Builder and save default settings
 *
 * @param array $story The reformatted StoryChief
 *
 * @return void
 */
function set_divi_settings($story) {
    $post_id = $story['external_id'];

    $page_layout = \Storychief\Settings\get_sc_option('divi_page_layout');
    if($page_layout){
        update_post_meta($post_id, '_et_pb_page_layout', $page_layout);
    }

    $hide_nav = \Storychief\Settings\get_sc_option('divi_hide_navigation');
    if($hide_nav){
        update_post_meta($post_id, '_et_pb_post_hide_nav', $hide_nav);
    }
}
if (isDiviThemeActive()) {
    add_action('storychief_after_publish_action', __NAMESPACE__ . '\set_divi_settings', 1);
}
