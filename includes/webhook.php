<?php

namespace Storychief\Webhook;

use WP_REST_Request;
use WP_Error;

function disable_cron() {
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/wp-json/storychief/') !== false) {
        if ( ! defined( 'DISABLE_WP_CRON' ) ) {
            define( 'DISABLE_WP_CRON', true );
        }

        remove_action('init', 'wp_cron');
        remove_action('wp_loaded', '_wp_cron');
    }
}
add_action('plugins_loaded', __NAMESPACE__ . '\disable_cron', 0, 20);

function register_routes() {
    register_rest_route('storychief', 'webhook', array(
        'methods'  => 'POST',
        'callback' =>  __NAMESPACE__ . '\handle',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', __NAMESPACE__ . '\register_routes');


/**
 * The Main webhook function, orchestrates the requested event to its corresponding function.
 *
 * @param WP_REST_Request $request
 * @return mixed
 */
function handle(WP_REST_Request $request) {
    storychief_debug_mode();

    $payload = json_decode($request->get_body(), true);

    if (!\Storychief\Tools\validMac($payload)) return new WP_Error('invalid_mac', 'The Mac is invalid', array('status' => 400));
    if (!isset($payload['meta']['event'])) return new WP_Error('no_event_type', 'The event is not set', array('status' => 400));

    $payload = apply_filters('storychief_before_handle_filter', $payload);

    if (isset($payload['meta']['fb-page-ids'])) {
        \Storychief\Settings\update_sc_option('meta_fb_pages', $payload['meta']['fb-page-ids']);
    }

    switch ($payload['meta']['event']) {
        case 'publish':
            $response = handlePublish($payload);
            break;
        case 'update':
            $response = handleUpdate($payload);
            break;
        case 'delete':
            $response = handleDelete($payload);
            break;
        case 'test':
            $response = handleConnectionCheck($payload);
            break;
        default:
            $response = missingMethod();
            break;
    }

    if (is_wp_error($response)) return $response;

    $response = apply_filters('storychief_alter_response', $response);

    if (!is_null($response)) $response  = \Storychief\Tools\appendMac($response);

    return rest_ensure_response($response);
}

/**
 * Handle a publish webhook call
 *
 * @param $payload
 * @return array
 */
function handlePublish($payload) {
    $story = $payload['data'];

    // Before publish action
    do_action('storychief_before_publish_action', array_merge($story));

    $is_draft = (bool)\Storychief\Settings\get_sc_option('test_mode');
    $is_draft = apply_filters('storychief_is_draft_status', $is_draft, $story);

    $post_type = \Storychief\Settings\get_sc_option('post_type') ? \Storychief\Settings\get_sc_option('post_type') : 'post';
    $post_type = apply_filters('storychief_change_post_type', $post_type, $story);

    $content = format_shortcodes($story['content']);
    $content = decode_gutenberg_blocks_html_entities($content);

    $post = array(
        'post_type'    => $post_type,
        'post_title'   => $story['title'],
        'post_content' => $content,
        'post_excerpt' => $story['excerpt'] ? $story['excerpt'] : '',
        'post_status'  => $is_draft ? 'draft' : 'publish',
        'post_author'  => null,
        'meta_input'   => array(),
    );

    // Set the slug
    if (isset($story['seo_slug']) && !empty($story['seo_slug'])) {
        $post['post_name'] = $story['seo_slug'];
    }

    if (isset($story['amphtml'])) {
        $post['meta_input']['_amphtml'] = $story['amphtml'];
    }

    $post_ID = safely_upsert_story($post);

    $story = array_merge($story, array('external_id' => $post_ID));

    // Author
    do_action('storychief_save_author_action', $story);

    // Tags
    do_action('storychief_save_tags_action', $story);

    // Categories
    do_action('storychief_save_categories_action', $story);

    // Featured Image
    do_action('storychief_save_featured_image_action', $story);

    // SEO
    do_action('storychief_save_seo_action', $story);

    // Sideload images
    do_action('storychief_sideload_images_action', $post_ID);

    // After publish action
    do_action('storychief_after_publish_action', $story);

    // generic WP cache flush scoped to a post ID.
    // well behaved caching plugins listen for this action.
    // WPEngine (which caches outside of WP) also listens for this action.
    clean_post_cache($post_ID);

    $permalink = apply_filters( 'storychief_publish_permalink', \Storychief\Tools\getPermalink($post_ID), $post_ID );

    return array(
        'id'        => $post_ID,
        'permalink' => $permalink,
    );
}

/**
 * Handle a update webhook call
 *
 * @param $payload
 * @return array|WP_Error
 */
function handleUpdate($payload) {
    $story = $payload['data'];

    if (!get_post_status($story['external_id'])) {
        return new WP_Error('post_not_found', 'The post could not be found', array('status' => 404));
    }

    // Before publish action
    do_action('storychief_before_publish_action', array_merge($story));

    $is_test_mode = (bool)\Storychief\Settings\get_sc_option('test_mode');
    $is_draft = apply_filters('storychief_is_draft_status', $is_test_mode, $story);

    $content = format_shortcodes($story['content']);
    $content = decode_gutenberg_blocks_html_entities($content);

    $post = array(
        'ID'           => $story['external_id'],
        'post_title'   => $story['title'],
        'post_content' => $content,
        'post_excerpt' => $story['excerpt'] ? $story['excerpt'] : '',
        'post_status'  => $is_draft ? 'draft' : 'publish',
        'meta_input'   => array(),
    );

    // Set the slug
    if (isset($story['seo_slug']) && !empty($story['seo_slug'])) {
        $post['post_name'] = $story['seo_slug'];
    }

    if (isset($story['amphtml'])) {
        $post['meta_input']['_amphtml'] = $story['amphtml'];
    }

    $post_ID = safely_upsert_story($post);

    $story = array_merge($story, array('external_id' => $post_ID));

    // Author
    do_action('storychief_save_author_action', $story);

    // Tags
    do_action('storychief_save_tags_action', $story);

    // Categories
    do_action('storychief_save_categories_action', $story);

    // Featured Image
    do_action('storychief_save_featured_image_action', $story);

    // SEO
    do_action('storychief_save_seo_action', $story);

    // Sideload images
    do_action('storychief_sideload_images_action', $post_ID);

    // After publish action
    do_action('storychief_after_publish_action', $story);

    // generic WP cache flush scoped to a post ID.
    // well behaved caching plugins listen for this action.
    // WPEngine (which caches outside of WP) also listens for this action.
    clean_post_cache($post_ID);

    $permalink = apply_filters( 'storychief_publish_permalink', \Storychief\Tools\getPermalink($post_ID), $post_ID );

    return array(
        'id'        => $post_ID,
        'permalink' => $permalink,
    );
}

/**
 * Handle a delete webhook call
 *
 * @param $payload
 * @return array
 */
function handleDelete($payload) {
    $story = $payload['data'];
    $post_ID = $story['external_id'];
    wp_delete_post($post_ID);

    do_action('storychief_after_delete_action', $story);

    return array(
        'id'        => $story['external_id'],
        'permalink' => null,
    );
}

/**
 * Handle a connection test webhook call
 * @param $payload
 * @return array
 */
function handleConnectionCheck($payload) {
    $story = $payload['data'];

    do_action('storychief_after_test_action', $story);

    return array();
}


/**
 * Handle calls to missing methods on the controller.
 *
 * @return mixed
 */
function missingMethod() {
    return;
}

/**
 * Safely save a story by disabling & re-enabling sanitation.
 *
 * @param $data
 * @return int
 */
function safely_upsert_story ($data) {
    // disable sanitation
    kses_remove_filters();

    if(isset($data['ID'])) {
        $post_ID = wp_update_post($data);
    } else {
        $post_ID = wp_insert_post($data);
    }

    // enable sanitation
    kses_init_filters();

    return $post_ID;
}

function format_shortcodes($content) {

    preg_match_all('/' . get_shortcode_regex() . '/', $content, $matches, PREG_SET_ORDER);

    foreach ($matches as $shortcode) {
        $shortcode_string = $shortcode[0];
        if ($shortcode_string) {
            $shortcode_string_formatted = str_replace(['&quot;', '”'], '"', $shortcode_string);
            $content = str_replace($shortcode_string, $shortcode_string_formatted, $content);
        }
    }

    return $content;
}

/**
 * Replaces html entities that are used in gutenberg blocks.
 */
function decode_gutenberg_blocks_html_entities($content) {

    preg_match_all('<!-- wp:(.*?)-->', $content, $matches, PREG_SET_ORDER); // Get all gutenberg blocks

    if (count($matches)){
        $content = str_replace("&lt;!--", "<!--", $content);
        $content = str_replace("--&gt;", "-->", $content);
        foreach ($matches as $block) {
            $block_json = $block[0];
            if ($block_json) {
                $block_json_formatted = str_replace(['&quot;', '”'], '"', $block_json);
                $content = str_replace($block_json, $block_json_formatted, $content);
            }
        }
    }

    return $content;
}

/**
 * Handle error reporting.
 * Hide all errors from displaying and add specific logging when debug mode is enabled
 */
function storychief_debug_mode()
{
    // We do this because some badly configured servers will return notices and warnings
    // that get prepended or appended to the rest response.
    ini_set('display_errors', 0);

    $is_debug = (bool)\Storychief\Settings\get_sc_option('debug_mode');

    if ($is_debug) {
        // Turn on error reporting.
        error_reporting(E_ALL);

        // Sets to log errors. Use 0 (or omit) to not log errors.
        ini_set('log_errors', 1);

        // Sets a log file path you can access in the theme editor.
        $log_path = STORYCHIEF_DIR . DIRECTORY_SEPARATOR . 'error.log';
        ini_set('error_log', $log_path);
    }
}
