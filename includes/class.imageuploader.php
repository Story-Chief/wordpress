<?php

namespace Storychief;

class ImageUploader
{
    private $post;
    private $alt;
    private $storychief_url;

    public $url;
    public $attachment_id;

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

        if (array_key_exists('host', $urlParts) === false) {
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
            $this->url = wp_get_attachment_url( $attachment->ID );
            $this->attachment_id = $attachment->ID;
            return true;
        }

        $allowed_filetypes = [
            'jpg|jpeg|jpe' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
        ];
        $wp_filetype = wp_check_filetype( basename( $this->storychief_url), $allowed_filetypes );

        if ( ! $wp_filetype['ext'] ) {
            return false;
        }

        $get = wp_remote_get( $this->storychief_url );

        $type = wp_remote_retrieve_header( $get, 'content-type' );

        if (!$type || strpos($type, 'image') === false) {
            return false;
        }

        $mirror = wp_upload_bits( basename( $this->storychief_url ), null, wp_remote_retrieve_body( $get ) );

        $attachment = array(
            'post_mime_type' => $type,
            'post_title'     => $this->alt ?: preg_replace('/\.[^.]+$/', '', basename($this->storychief_url)),
            'post_content'   => '',
            'post_status'    => 'inherit',
        );

        $attach_id = wp_insert_attachment( $attachment, $mirror['file'], $this->post->ID );
        update_post_meta( $attach_id, '_storychief_source_url', $this->storychief_url );

        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $attach_data = wp_generate_attachment_metadata( $attach_id, $mirror['file'] );

        wp_update_attachment_metadata( $attach_id, $attach_data );

        $this->attachment_id = $attach_id;
        $this->url = $mirror['url'];
        return true;
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
