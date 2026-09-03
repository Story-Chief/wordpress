<?php

namespace Storychief;

class ImageUploader
{
    private $post;
    private $alt;
    private $storychief_url;

    public $url;
    public $attachment_id;
    public $error;

    public function __construct($url, $alt, $post)
    {
        $this->post = $post;
        $this->storychief_url = $url;
        $this->alt = $alt;
    }

    /**
     * Return host of url simplified without www
     * @param null|string $url
     * @param bool $scheme
     * @return null|string
     */
    public static function getHostUrl($url = null, $scheme = false)
    {
        $url = $url ?: site_url();

        $urlParts = wp_parse_url($url);

        if (!is_array($urlParts) || !isset($urlParts['host'])) {
            return null;
        }

        $url = array_key_exists('port', $urlParts) ? $urlParts['host'] . ":" . $urlParts['port'] : $urlParts['host'];
        $urlSimplified = preg_split('/^(www(2|3)?\.)/i', $url, -1, PREG_SPLIT_NO_EMPTY); // Delete www from URL
        $urlSimplified = is_array($urlSimplified) && array_key_exists(0, $urlSimplified) ? $urlSimplified[0] : $url;
        $url = $scheme && array_key_exists('scheme', $urlParts) ? $urlParts['scheme'] . '://' . $urlSimplified : $urlSimplified;

        return $url;
    }

    /**
     * Check url is allowed to upload or not
     * @return bool
     */
    public function validate()
    {
        $host_url = self::getHostUrl($this->storychief_url);
        $site_host_url = self::getHostUrl();

        if ($host_url === $site_host_url || !$host_url) {
            return false;
        }
        // Todo: add a check to only side-load urls from storychief
        return true;
    }

    /**
     * Save image on wp_upload_dir
     * Add image to the media library and attach in the post
     * @return bool
     */
    public function save()
    {
        if($attachment = $this->get_attachment_by_storychief_source_url($this->storychief_url)) {
            $metadata = wp_get_attachment_metadata($attachment->ID);
            if (wp_attachment_is_image($attachment->ID) && !empty($metadata['width']) && !empty($metadata['height'])) {
                $this->url = wp_get_attachment_url($attachment->ID);
                $this->attachment_id = (int) $attachment->ID;
                return true;
            }
        }

        // Parse the path, not the entire URL: query-string URLs are valid too.
        $filename = wp_basename((string) wp_parse_url($this->storychief_url, PHP_URL_PATH));
        $wp_filetype = wp_check_filetype($filename);
        if (!$wp_filetype['type'] || strpos($wp_filetype['type'], 'image/') !== 0) {
            return $this->fail(new \WP_Error('storychief_image_type', 'The image URL has an unsupported file type.'));
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        // Streams to disk, validates HTTP responses and uses safe redirects.
        $temporary_file = download_url($this->storychief_url, 30);
        if (is_wp_error($temporary_file)) {
            return $this->fail($temporary_file);
        }
        if (!wp_get_image_mime($temporary_file) || !wp_getimagesize($temporary_file)) {
            wp_delete_file($temporary_file);
            return $this->fail(new \WP_Error('storychief_invalid_image', 'The downloaded file is not a supported image.'));
        }

        $file = array('name' => $filename, 'tmp_name' => $temporary_file);
        // Import one source; let WordPress generate its configured sizes and metadata.
        $attachment_id = media_handle_sideload($file, $this->post->ID, $this->alt ?: preg_replace('/\.[^.]+$/', '', $filename));
        if (is_wp_error($attachment_id)) {
            wp_delete_file($temporary_file);
            return $this->fail($attachment_id);
        }

        update_post_meta($attachment_id, '_storychief_source_url', $this->storychief_url);
        update_post_meta($attachment_id, '_wp_attachment_image_alt', (string) $this->alt);
        $this->attachment_id = (int) $attachment_id;
        $this->url = wp_get_attachment_url($attachment_id);
        return true;
    }

    private function fail($error)
    {
        $this->error = $error;
        self::report_error($error, $this->storychief_url, $this->post->ID);
        return false;
    }

    public static function report_error($error, $url, $post_id)
    {
        // Keep potentially signed/private URLs out of logs, but expose error details to integrations.
        do_action('storychief_image_sideload_error', $error, $url, $post_id);
        error_log(sprintf('StoryChief image sideload failed for post %d: %s', $post_id, $error->get_error_code()));
    }

    private function get_attachment_by_storychief_source_url( $source_url ) {
        $args = array(
            'post_type'   => 'attachment',
            'post_status' => 'inherit', // attachments usually have this status
            'meta_query'  => array(
                array(
                    'key'   => '_storychief_source_url',
                    'value' => $source_url,
                ),
            ),
            'posts_per_page' => 1,
        );

        $attachments = get_posts( $args );

        return ! empty( $attachments ) ? $attachments[0] : null;
    }
}
