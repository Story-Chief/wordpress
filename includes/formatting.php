<?php

namespace Storychief\Formatting;

/**
 * Adds fb:pages as meta tag to header
 * Needed for activation of FB Instant Articles.
 */
function meta_instant_articles() {
    echo '<meta property="fb:pages" content="' . \Storychief\Settings\get_sc_option('meta_fb_pages') . '" />' . PHP_EOL;
}
add_action('wp_head', __NAMESPACE__ . '\meta_instant_articles');


/**
 * Adds amphtml as meta tag to header
 * Links to the AMP version of the article
 */
function meta_amp() {
    global $post;
    if (!empty($post) && is_singular()) {
        $ampHtmlLink = get_post_meta($post->ID, '_amphtml', true);
        if (!empty($ampHtmlLink)) {
            echo '<link rel="amphtml" href="' . $ampHtmlLink . '" />' . PHP_EOL;
        }
    }
}
add_action('wp_head', __NAMESPACE__ . '\meta_amp');


/**
 * Set the Meta title
 * @return mixed
 */
function meta_title() {
    global $post;
    if (!\Storychief\Tools\isAnySeoPluginActive() && !empty($post) && is_singular()) {
        $seoTitle = get_post_meta($post->ID, '_yoast_wpseo_title', true);
        return $seoTitle;
    }
}
add_filter('pre_get_document_title', __NAMESPACE__ . '\meta_title', 10);

/**
 * Set the Meta description
 * @return mixed
 */
function meta_description() {
    global $post;
    if (!\Storychief\Tools\isAnySeoPluginActive() && !empty($post) && is_singular()) {
        $seoDescription = get_post_meta($post->ID, '_yoast_wpseo_metadesc', true);
        if (!empty($seoDescription)) {
            echo '<meta name="description" content="' . $seoDescription . '" />' . PHP_EOL;
        }
    }
}
add_action('wp_head', __NAMESPACE__ . '\meta_description', 10);

/**
 * Set the Canonical URL
 * @return mixed
 */
function meta_canonical() {
    global $post;
    if (!\Storychief\Tools\isAnySeoPluginActive() && !empty($post) && is_singular()) {
        $canonicalUrl = get_post_meta($post->ID, '_yoast_wpseo_canonical', true);
        if (!empty($canonicalUrl)) {
            echo '<link rel="canonical" href="' . $canonicalUrl . '" />' . PHP_EOL;
        }
    }
}
add_action('wp_head', __NAMESPACE__ . '\meta_canonical', 10);

/**
 * Adds og-information as meta tag to header
 */
function open_graph_tags() {
    global $post;
    if (!\Storychief\Tools\isOpenGraphHandled() && !empty($post) && is_singular()) {
        $seoTitle = get_post_meta($post->ID, '_yoast_wpseo_title', true);
        $seoDescription = get_post_meta($post->ID, '_yoast_wpseo_metadesc', true);

        echo '<meta property="og:type" content="article">' . PHP_EOL;
        echo '<meta name="twitter:card" content="summary">' . PHP_EOL;

        if (!empty($seoTitle)) {
            echo '<meta property="og:title" content="'.$seoTitle.'">' . PHP_EOL;
            echo '<meta property="twitter:title" content="'.$seoTitle.'">' . PHP_EOL;
        }
        if (!empty($seoDescription)) {
            echo '<meta property="og:description" content="'.$seoDescription.'">' . PHP_EOL;
            echo '<meta property="twitter:description" content="'.$seoDescription.'">' . PHP_EOL;
        }

        if (has_post_thumbnail( $post->ID )) {
            $attachment_id = get_post_thumbnail_id($post->ID);
            $image_attributes = wp_get_attachment_image_src($attachment_id, 'full');
            if (is_array($image_attributes)) {
                echo '<meta property="og:image" content="'.$image_attributes[0].'">' . PHP_EOL;
                echo '<meta property="og:image:width" content="'.$image_attributes[1].'">' . PHP_EOL;
                echo '<meta property="og:image:height" content="'.$image_attributes[2].'">' . PHP_EOL;
                echo '<meta property="twitter:image" content="'.$image_attributes[0].'">' . PHP_EOL;
            }
        }
    }
}
add_action('wp_head', __NAMESPACE__ . '\open_graph_tags', 5);

/**
 * Proper way to enqueue scripts and styles
 */
function enqueue_styles()
{
    wp_register_style( 'sc-caption-style', plugins_url('story-chief/css/captions.css'), false, '1' );
    wp_register_style( 'sc-video-style', plugins_url('story-chief/css/videos.css'), false, '1' );
    wp_register_style( 'sc-alignment-style', plugins_url('story-chief/css/alignment.css'), false, '1' );

    if (\Storychief\Settings\get_sc_option('styling_caption')) {
        wp_enqueue_style('sc-caption-style');
    }
    if (\Storychief\Settings\get_sc_option('styling_video')) {
        wp_enqueue_style('sc-video-style');
    }
    if (\Storychief\Settings\get_sc_option('styling_align')) {
        wp_enqueue_style('sc-alignment-style');
    }
}

add_action('wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_styles');
