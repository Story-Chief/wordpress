<?php

namespace Storychief\Mapping;

use Storychief\ImageUploader;

/**
 * Attach author
 *
 * @param $story
 */
function saveAuthor($story)
{
    // Author
    if (isset($story['author']['data']['email'])) {
        $user_id = email_exists($story['author']['data']['email']);

        if (!$user_id && \Storychief\Settings\get_sc_option('author_create')) {
            $user_id = wp_create_user($story['author']['data']['email'], '', $story['author']['data']['email']);
            wp_update_user(array(
                'ID'            => $user_id,
                'first_name'    => $story['author']['data']['first_name'],
                'last_name'     => $story['author']['data']['last_name'],
                'display_name'  => $story['author']['data']['first_name'] . ' ' . $story['author']['data']['last_name'],
                'user_nicename' => $story['author']['data']['first_name'] . ' ' . $story['author']['data']['last_name'],
                'description'   => $story['author']['data']['bio'],
                'role'          => 'author',
            ));
        }

        if ($user_id) {
            \Storychief\Webhook\safely_upsert_story(array(
                'ID'          => $story['external_id'],
                'post_author' => $user_id
            ));
        }
    }
}
add_action('storychief_save_author_action', __NAMESPACE__ . '\saveAuthor');

/**
 * Sync tags
 *
 * @param $story
 * @return array
 */
function saveTags($story)
{
    if (!function_exists('wp_create_tag')) {
        require_once(ABSPATH . 'wp-admin/includes/taxonomy.php');
    }

    if (isset($story['tags']['data'])) {
        $tags = array();
        foreach ($story['tags']['data'] as $tag) {
            $term = get_term_by('slug', $tag['slug'], 'post_tag');
            if (!$term) {
                $term = get_term_by('name', $tag['name'], 'post_tag');
            }

            if ($term) {
                $tags[] = (int)$term->term_id;
            } elseif (\Storychief\Settings\get_sc_option('tag_create')) {
                $tag = wp_create_tag($tag['name']);
                $tags[] = (int) $tag['term_id'];
            }
        }

        wp_set_post_tags($story['external_id'], $tags, false);
    }

    return $story;
}
add_action('storychief_save_tags_action', __NAMESPACE__ . '\saveTags');

/**
 * Sync categories
 *
 * @param $story
 * @return array
 */
function saveCategories($story)
{
    if (!function_exists('wp_create_category')) {
        require_once(ABSPATH . 'wp-admin/includes/taxonomy.php');
    }

    if (isset($story['categories']['data'])) {
        $categories = array();
        foreach ($story['categories']['data'] as $category) {
            $term = get_term_by('slug', $category['slug'], 'category');
            if (!$term) {
                $term = get_term_by('name', $category['name'], 'category');
            }

            if ($term) {
                $categories[] = (int)$term->term_id;
            } elseif (\Storychief\Settings\get_sc_option('category_create')) {
                $categories[] = (int)wp_create_category($category['name']);
            }
        }
        wp_set_post_categories($story['external_id'], $categories, false);
    }
    return $story;
}
add_action('storychief_save_categories_action', __NAMESPACE__ . '\saveCategories');

/**
 * Save SEO fields
 *
 * @param $story
 * @return array
 */
function saveSEOFields($story)
{
    if (\Storychief\Tools\isYoastPluginActive()) {
        saveYoastSeoFields($story);
    } else if (\Storychief\Tools\isSeopressPluginActive()) {
        saveSeopressSeoFields($story);
    } else if (\Storychief\Tools\isAllInOneSeoPackPluginActive()) {
        saveAllInOneSeoPackFields($story);
    } else if (\Storychief\Tools\isRankMathPluginActive()) {
        saveRankMathFields($story);
    }

    // Always save for StoryChief too
    // we default to the same naming as Yoast for compatibility reasons
    if (isset($story['seo_title']) && !empty($story['seo_title'])) {
        update_post_meta($story['external_id'], '_yoast_wpseo_title', $story['seo_title']);
    }
    if (isset($story['seo_description']) && !empty($story['seo_description'])) {
        update_post_meta($story['external_id'], '_yoast_wpseo_metadesc', $story['seo_description']);
    }
    if (isset($story['canonical']) && !empty($story['canonical'])) {
        update_post_meta($story['external_id'], '_yoast_wpseo_canonical', $story['canonical']);
    }

    return $story;
}
add_action('storychief_save_seo_action', __NAMESPACE__ . '\saveSEOFields');

function saveYoastSeoFields($story)
{
    // Temporarily disable Yoast meta data filters
    remove_filter('update_post_metadata', array('WPSEO_Meta', 'remove_meta_if_default'));
    remove_filter('add_post_metadata', array('WPSEO_Meta', 'dont_save_meta_if_default'));
    remove_filter('sanitize_post_meta__yoast_wpseo_title', array('WPSEO_Meta', 'sanitize_post_meta'));
    remove_filter('sanitize_post_meta__yoast_wpseo_metadesc', array('WPSEO_Meta', 'sanitize_post_meta'));
    remove_filter('sanitize_post_meta__yoast_wpseo_focuskw', array('WPSEO_Meta', 'sanitize_post_meta'));
    remove_filter('sanitize_post_meta__yoast_wpseo_focuskw_text_input', array('WPSEO_Meta', 'sanitize_post_meta'));
    remove_filter('sanitize_post_meta__yoast_wpseo_linkdex', array('WPSEO_Meta', 'sanitize_post_meta'));
    remove_filter('sanitize_post_meta__yoast_wpseo_content_score', array('WPSEO_Meta', 'sanitize_post_meta'));

    if (isset($story['seo_keywords']) && isset($story['seo_keywords']['data']) && !empty($story['seo_keywords']['data'])) {
        $keyword = $story['seo_keywords']['data'][0]['name'];
        update_post_meta($story['external_id'], '_yoast_wpseo_focuskw', $keyword);
        update_post_meta($story['external_id'], '_yoast_wpseo_focuskw_text_input', $keyword);
    }

    if (isset($story['seo_score']) && !empty($story['seo_score'])) {
        update_post_meta($story['external_id'], '_yoast_wpseo_linkdex', $story['seo_score']);
    }

    if (isset($story['readability_score']) && !empty($story['readability_score'])) {
        update_post_meta($story['external_id'], '_yoast_wpseo_content_score', $story['readability_score']);
    }
}

function saveSeopressSeoFields($story)
{
    if (isset($story['seo_title']) && !empty($story['seo_title'])) {
        // _seopress_titles_title _seopress_social_fb_title _seopress_social_twitter_title
        update_post_meta($story['external_id'], '_seopress_titles_title', $story['seo_title']);
    }
    if (isset($story['seo_description']) && !empty($story['seo_description'])) {
        // _seopress_titles_desc _seopress_social_fb_desc _seopress_social_twitter_desc
        update_post_meta($story['external_id'], '_seopress_titles_desc', $story['seo_description']);
    }
    if (isset($story['seo_keywords']) && isset($story['seo_keywords']['data']) && !empty($story['seo_keywords']['data'])) {
        $keyword = $story['seo_keywords']['data'][0]['name'];
        update_post_meta($story['external_id'], '_seopress_analysis_target_kw', $keyword);
    }
    if (isset($story['canonical']) && !empty($story['canonical'])) {
        update_post_meta($story['external_id'], '_seopress_robots_canonical', $story['canonical']);
    }
}

function saveAllInOneSeoPackFields($story)
{
    if (isset($story['seo_title']) && !empty($story['seo_title'])) {
        // _seopress_titles_title _seopress_social_fb_title _seopress_social_twitter_title
        update_post_meta($story['external_id'], '_aioseop_title', $story['seo_title']);
    }
    if (isset($story['seo_description']) && !empty($story['seo_description'])) {
        // _seopress_titles_desc _seopress_social_fb_desc _seopress_social_twitter_desc
        update_post_meta($story['external_id'], '_aioseop_description', $story['seo_description']);
    }
    if (isset($story['canonical']) && !empty($story['canonical'])) {
        update_post_meta($story['external_id'], '_aioseop_custom_link', $story['canonical']);
    }
}

function saveRankMathFields($story)
{
    if (isset($story['seo_title']) && !empty($story['seo_title'])) {
        update_post_meta($story['external_id'], 'rank_math_title', $story['seo_title']);
    }
    if (isset($story['seo_description']) && !empty($story['seo_description'])) {
        update_post_meta($story['external_id'], 'rank_math_description', $story['seo_description']);
    }
    if (isset($story['seo_keywords']) && isset($story['seo_keywords']['data']) && !empty($story['seo_keywords']['data'])) {
        $keyword = $story['seo_keywords']['data'][0]['name'];
        update_post_meta($story['external_id'], 'rank_math_focus_keyword', $keyword);
    }
    if (isset($story['seo_score']) && !empty($story['seo_score'])) {
        update_post_meta($story['external_id'], 'rank_math_seo_score', $story['seo_score']);
    }
    if (isset($story['canonical']) && !empty($story['canonical'])) {
        update_post_meta($story['external_id'], 'rank_math_canonical_url', $story['canonical']);
    }
}

/**
 * Save Featured Image
 *
 * @param $story
 * @return array
 */
function saveFeaturedImage($story)
{
    if (isset($story['featured_image']['data']['sizes']['full'])) {
        $post = get_post($story['external_id']);
        $image_url = $story['featured_image']['data']['sizes']['full'];
        $alt = (isset($story['featured_image']['data']['alt']) && $story['featured_image']['data']['alt']) ? $story['featured_image']['data']['alt'] : $post->post_title . " cover";

        $uploader = new ImageUploader($image_url, $alt, $post);
        $uploader->save();
        $attachment_id = $uploader->attachment_id;

        if (is_integer($attachment_id)) {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
            set_post_thumbnail($post->ID, $attachment_id);
        }
    }

    return $story;
}
add_action('storychief_save_featured_image_action', __NAMESPACE__ . '\saveFeaturedImage');


/**
 * Side load all images from a post
 *
 * @param \WP_Post $post
 * @return bool
 */
function sideloadImages(\WP_Post $post)
{
    $content = $post->post_content;
    $images = \Storychief\Tools\findAllImageUrls($content);

    if ($images === null || empty($images)) {
        return false;
    }

    foreach ($images as $image) {
        $uploader = new ImageUploader($image['url'], $image['alt'], $post);
        if ($uploader->validate() && $uploader->save() !== false) {
            $urlParts = parse_url($uploader->url);
            $base_url = $uploader::getHostUrl(null, true);
            $image_url = $base_url . $urlParts['path'];
            $content = preg_replace('/' . preg_quote($image['url'], '/') . '/', $image_url, $content);
        }
    }
    $updated_post = array(
        'ID' => $post->ID,
        'post_content' => $content,
    );

    \Storychief\Webhook\safely_upsert_story($updated_post);

    // generic WP cache flush scoped to a post ID.
    // well behaved caching plugins listen for this action.
    // WPEngine (which caches outside of WP) also listens for this action.
    clean_post_cache($post->ID);
}
add_action('wp_async_storychief_sideload_images_action', __NAMESPACE__ . '\sideloadImages');
