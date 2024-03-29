<?php

namespace Storychief\Tools;

/**
 * Check if Yoast plugin is active
 * @return bool
 */
function isYoastPluginActive() {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    return is_plugin_active('wordpress-seo/wp-seo.php') || is_plugin_active('wordpress-seo-premium/wp-seo-premium.php');
}

/**
 * Check if SeoPress plugin is active
 * @return bool
 */
function isSeopressPluginActive() {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    return is_plugin_active('wp-seopress/seopress.php');
}

/**
 * Check if All In One SEO Pack is active
 * @return bool
 */
function isAllInOneSeoPackPluginActive() {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    return is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php') || is_plugin_active('all-in-one-seo-pack-pro/all_in_one_seo_pack.php');
}

/**
 * Check if Rank Math is active
 * @return bool
 */
function isRankMathPluginActive() {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    return is_plugin_active('seo-by-rank-math/rank-math.php');
}

/**
 * Check if any SEO plugin is active
 * @return bool
 */
function isAnySeoPluginActive() {
    return (isYoastPluginActive() || isSeopressPluginActive() || isAllInOneSeoPackPluginActive() || isRankMathPluginActive());
}

/**
 * Check if a plugin is active that handles Open Graph
 * @return bool
 */
function isOpenGraphHandled() {
    return (isYoastPluginActive() || isSeopressPluginActive() || isRankMathPluginActive());
}

/**
 * Append a MAC to the given payload.
 *
 * @param  $payload
 * @return array
 */
function appendMac($payload) {
    $payload['mac'] = hash_hmac('sha256', json_encode($payload), \Storychief\Settings\get_sc_option('encryption_key'));

    return $payload;
}

/**
 * Determine if the MAC for the given payload is valid.
 *
 * @param  $payload
 * @return bool
 */
function validMac($payload) {
    if (isset($payload['meta']['mac'])) {
        $givenMac = $payload['meta']['mac'];
        unset($payload['meta']['mac']);
        $calcMac = hash_hmac('sha256', json_encode($payload), \Storychief\Settings\get_sc_option('encryption_key'));

        return hash_equals($givenMac, $calcMac);
    }

    return false;
}

/**
 * Get the permalink of a post
 * @param $post_ID
 * @return string|null
 */
function getPermalink($post_ID) {
    if(get_post_status($post_ID) !== 'publish'){
        return get_preview_post_link($post_ID);
    }

    return get_permalink($post_ID);
}

/**
 * Find image urls in content and retrieve urls by array
 * @param $content
 * @return array|null
 */
function findAllImageUrls($content) {
    $pattern = '/<img[^>]*src=["\']([^"\']*)[^"\']*["\'][^>]*>/i'; // find img tags and retrieve src
    preg_match_all($pattern, $content, $urls, PREG_SET_ORDER);
    if (empty($urls)) {
        return null;
    }
    foreach ($urls as $index => &$url) {
        $images[$index]['alt'] = preg_match('/<img[^>]*alt=["\']([^"\']*)[^"\']*["\'][^>]*>/i', $url[0], $alt) ? $alt[1] : null;
        $images[$index]['url'] = $url = $url[1];
    }
    foreach (array_unique($urls) as $index => $url) {
        $unique_array[] = $images[$index];
    }
    return $unique_array;
}
