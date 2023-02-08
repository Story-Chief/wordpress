<?php

namespace Storychief;

class ImageUploader
{
    public $post;
    public $url;
    public $alt;
    public $filename;
    public $attachment_id;

    public function __construct($url, $alt, $post)
    {
        $this->post = $post;
        $this->url = $url;
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

        $urlParts = parse_url($url);

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
        $host_url = self::getHostUrl($this->url);
        $site_host_url = self::getHostUrl();

        if ($host_url === $site_host_url || !$host_url) {
            return false;
        }
        // Todo: add a check to only side-load urls from storychief
        return true;
    }

    /**
     * Return custom image name with user rules
     * @return string Custom file name
     */
    public function getFilename()
    {
        $filename = basename($this->url);
        $this->filename = $filename;
        return $filename;
    }

    /**
     * Save image on wp_upload_dir
     * Add image to the media library and attach in the post
     * @return bool
     */
    public function save()
    {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        if (function_exists('curl_init') === false) return false;

        setlocale(LC_ALL, "en_US.UTF8");
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $image_data = curl_exec($ch);

        if ($image_data === false) return false;

        $image_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        if (strpos($image_type, 'image') === false) {
            return false;
        }

        $image_name = $this->getFilename();
        $upload_dir = wp_upload_dir(date('Y/m', $this->post->post_date ? strtotime($this->post->post_date) : time()));
        $image_path = urldecode($upload_dir['path'] . '/' . $image_name);
        $image_url = urldecode($upload_dir['url'] . '/' . $image_name);

        // check if file with same name exists in upload path
        if (is_file($image_path)) {
            $this->url = $image_url;
            // If the image already exists, get the id to link it to the post
            $attachment_id = attachment_url_to_postid($image_url);
            if ($attachment_id) {
                $this->attachment_id = $attachment_id;
                curl_close($ch);
                return true;
            } else {
                $num = uniqid();
                $image_path = urldecode($upload_dir['path'] . '/' . $num . '_' . $image_name);
                $image_url = urldecode($upload_dir['url'] . '/' . $num . '_' . $image_name);
            }
        }

        curl_close($ch);
        file_put_contents($image_path, $image_data);

        if (is_file($image_path) === false) {
            return false;
        }

        $this->attachment_id = $this->attachImage($image_path, $image_url, $image_name);

        $this->url = $image_url;
        return true;
    }

    /**
     * Attach image to post and media management
     * @param string $path Image path
     * @param string $url Image url
     * @param string $name Image name
     * @return int $attach_id
     */
    public function attachImage($path, $url, $name)
    {
        $fileType = wp_check_filetype($path);
        $attachment = array(
            'guid'           => $url,
            'post_mime_type' => $fileType['type'],
            'post_title'     => $this->alt ?: preg_replace('/\.[^.]+$/', '', $name),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );
        $attach_id = wp_insert_attachment($attachment, $path, $this->post->ID);
        $attach_data = wp_generate_attachment_metadata($attach_id, $path);

        wp_update_attachment_metadata($attach_id, $attach_data);

        return $attach_id;
    }
}
