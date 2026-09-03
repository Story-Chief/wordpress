<?php

namespace Storychief;

/** Import the highest-resolution source, keeping the article's image HTML minimal. */
class ResponsiveImages
{
    private $attachments = array();
    private $source_removal_attribute;

    /**
     * Parse width/density descriptors, not filenames or assumed pixel sizes.
     * Commas within URLs are significant (e.g. transformation query parameters).
     * Invalid/mixed lists are left untouched rather than silently dropping sources.
     *
     * @return array|\WP_Error
     */
    public static function candidates($srcset, $src = '')
    {
        $candidates = array();
        $type = null;
        $seen = array();
        $remaining = trim((string) $srcset);
        while ($remaining !== '') {
            $remaining = ltrim($remaining, " \t\n\r\f,");
            if ($remaining === '') {
                break;
            }
            preg_match('/^([^\x20\t\n\r\f]+)(.*)$/s', $remaining, $match);
            $url = $match[1];
            $remaining = $match[2];
            $descriptor = '';
            if (substr($url, -1) === ',') {
                $url = rtrim($url, ',');
            } else {
                $parts = explode(',', $remaining, 2);
                $descriptor = trim($parts[0]);
                $remaining = isset($parts[1]) ? $parts[1] : '';
            }
            if ($descriptor === '') {
                $value = 1;
                $unit = 'x';
            } elseif (preg_match('/^([0-9]+)w$/', $descriptor, $match)) {
                $value = (float) $match[1];
                $unit = 'w';
            } elseif (preg_match('/^((?:[0-9]+(?:\.[0-9]+)?|\.[0-9]+)(?:[eE][+-]?[0-9]+)?)x$/', $descriptor, $match)) {
                $value = (float) $match[1];
                $unit = 'x';
            } else {
                return new \WP_Error('storychief_invalid_srcset', 'Unrecognized image source-set descriptor.');
            }
            if (!$url || $value <= 0 || !is_finite($value) || $value > PHP_INT_MAX || ($type && $type !== $unit) || isset($seen[(string) $value])) {
                return new \WP_Error('storychief_invalid_srcset', 'Invalid or mixed image source-set descriptors.');
            }
            $type = $unit;
            $seen[(string) $value] = true;
            $candidates[] = array('url' => $url, 'value' => $value, 'unit' => $unit);
        }
        // src is an implicit 1x candidate only for density-based sets.
        if ($src && (!$candidates || ($type === 'x' && !isset($seen['1'])))) {
            $candidates[] = array('url' => $src, 'value' => 1, 'unit' => 'x');
        }
        usort($candidates, function ($a, $b) { return $b['value'] <=> $a['value']; });
        return $candidates;
    }

    public function sideload($content, $post)
    {
        if (!class_exists('WP_HTML_Tag_Processor')) {
            ImageUploader::report_error(new \WP_Error('storychief_wordpress_version', 'Responsive sideloading requires WordPress 6.2.'), '', $post->ID);
            return $content;
        }
        $tags = new \WP_HTML_Tag_Processor($content);
        // Only source tags visited by the HTML parser receive this unguessable marker.
        // It lets us remove them without reserializing the surrounding article HTML.
        $this->source_removal_attribute = 'data-storychief-remove-' . str_replace('-', '', wp_generate_uuid4());
        while ($tags->next_tag(array('tag_closers' => 'visit'))) {
            $tag = $tags->get_tag();
            if ($tag === 'PICTURE' && !$tags->is_tag_closer()) {
                $this->sideload_picture($tags, $post);
                continue;
            }
            if ($tags->is_tag_closer() || $tag !== 'IMG') {
                continue;
            }
            // Also simplify markup produced by the previous responsive implementation
            // when a post is explicitly reprocessed. Never re-download its attachment.
            $managed_id = (int) $tags->get_attribute('data-storychief-image-id');
            if ($managed_id && wp_get_attachment_url($managed_id) && get_post_meta($managed_id, '_storychief_source_url', true)) {
                $id = $managed_id;
            } else {
                $src = (string) $tags->get_attribute('src');
                $candidates = self::candidates($tags->get_attribute('srcset'), $src);
                if (is_wp_error($candidates)) {
                    ImageUploader::report_error($candidates, $src, $post->ID);
                    continue;
                }
                $has_remote = false;
                foreach ($candidates as $candidate) {
                    if ($this->is_remote($candidate['url'])) {
                        $has_remote = true;
                    }
                }
                if (!$has_remote) {
                    continue;
                }
                $url = $candidates[0]['url'];
                if (strpos($url, '//') === 0) {
                    $url = 'https:' . $url;
                }
                $id = $this->attachment($url, (string) $tags->get_attribute('alt'), $post);
            }
            if (!$id) {
                // Keep the complete original element on failure, so it can be retried.
                continue;
            }
            if (!$this->rewrite_image($tags, $id)) {
                ImageUploader::report_error(new \WP_Error('storychief_image_metadata', 'Image dimensions are missing.'), '', $post->ID);
            }
        }
        $attributes = "(?:[^>\"']|\"[^\"]*\"|'[^']*')*";
        $html = $tags->get_updated_html();
        if (strpos($html, $this->source_removal_attribute) === false) {
            return $html;
        }
        $simplified = preg_replace(
            '/<source\b' . $attributes . '\s' . $this->source_removal_attribute . '="1"' . $attributes . '>/i',
            '',
            $html
        );
        if ($simplified === null || strpos($simplified, $this->source_removal_attribute) !== false) {
            ImageUploader::report_error(new \WP_Error('storychief_markup_cleanup', 'Image markup could not be simplified.'), '', $post->ID);
            return $content;
        }
        return $simplified;
    }

    /** Treat a picture and all its sources as one image, not separate imports. */
    private function sideload_picture($tags, $post)
    {
        $tags->set_bookmark('storychief_picture');
        $sources = array();
        $image = null;
        $error = null;
        $depth = 1;
        while ($tags->next_tag(array('tag_closers' => 'visit'))) {
            if ($tags->get_tag() === 'PICTURE') {
                $depth += $tags->is_tag_closer() ? -1 : 1;
                if ($depth === 0) {
                    break;
                }
                $error = new \WP_Error('storychief_invalid_picture', 'Nested picture elements are not supported.');
            }
            if ($depth !== 1 || $tags->is_tag_closer()) {
                continue;
            }
            if ($tags->get_tag() === 'SOURCE') {
                $candidates = self::candidates($tags->get_attribute('srcset'));
                if (is_wp_error($candidates)) {
                    $error = $candidates;
                } else {
                    $sources = array_merge($sources, $candidates);
                }
            } elseif ($tags->get_tag() === 'IMG') {
                if ($image !== null) {
                    $error = new \WP_Error('storychief_invalid_picture', 'A picture must contain a single img element.');
                }
                $image = array(
                    'src' => (string) $tags->get_attribute('src'),
                    'srcset' => (string) $tags->get_attribute('srcset'),
                    'alt' => (string) $tags->get_attribute('alt'),
                    'width' => (int) $tags->get_attribute('width'),
                );
            }
        }
        if (!$image || $depth !== 0 || $error) {
            ImageUploader::report_error($error ?: new \WP_Error('storychief_invalid_picture', 'An incomplete picture was left unchanged.'), '', $post->ID);
            return;
        }
        $image_candidates = self::candidates($image['srcset'], $image['src']);
        if (is_wp_error($image_candidates)) {
            ImageUploader::report_error($image_candidates, $image['src'], $post->ID);
            return;
        }
        // An unannotated fallback src has no advertised resolution. Use it only
        // if there are no source candidates; otherwise compare the advertised sets.
        $candidates = $image['srcset'] !== '' || !$sources
            ? array_merge($image_candidates, $sources) : $sources;
        $has_remote = false;
        $units = array();
        foreach ($candidates as $candidate) {
            $units[$candidate['unit']] = true;
            $has_remote = $has_remote || $this->is_remote($candidate['url']);
        }
        if (!$has_remote) {
            return;
        }
        $width = $image['width'];
        if (!$width) {
            foreach ($image_candidates as $candidate) {
                if ($candidate['url'] === $image['src'] && $candidate['unit'] === 'w') {
                    $width = $candidate['value'];
                }
            }
        }
        if (count($units) > 1 && !$width) {
            ImageUploader::report_error(new \WP_Error('storychief_ambiguous_picture', 'Cannot compare width and density candidates without image dimensions.'), $image['src'], $post->ID);
            return;
        }
        $selected = null;
        $largest = 0;
        foreach ($candidates as $candidate) {
            $resolution = count($units) > 1 && $candidate['unit'] === 'x'
                ? $candidate['value'] * $width : $candidate['value'];
            if ($resolution > $largest) {
                $largest = $resolution;
                $selected = $candidate['url'];
            }
        }
        if (strpos($selected, '//') === 0) {
            $selected = 'https:' . $selected;
        }
        $id = $this->attachment($selected, $image['alt'], $post);
        $metadata = $id ? wp_get_attachment_metadata($id) : false;
        if (!$id || empty($metadata['width']) || empty($metadata['height']) || !wp_get_attachment_url($id)) {
            // Do not remove the original sources if the selected import failed.
            return;
        }
        $tags->seek('storychief_picture');
        while ($tags->next_tag(array('tag_closers' => 'visit'))) {
            if ($tags->get_tag() === 'PICTURE' && $tags->is_tag_closer()) {
                break;
            }
            if (!$tags->is_tag_closer()) {
                if ($tags->get_tag() === 'SOURCE') {
                    $tags->set_attribute($this->source_removal_attribute, '1');
                } elseif ($tags->get_tag() === 'IMG') {
                    $this->rewrite_image($tags, $id);
                }
            }
        }
    }

    private function rewrite_image($tags, $id)
    {
        $metadata = wp_get_attachment_metadata($id);
        $url = wp_get_attachment_url($id);
        if (empty($metadata['width']) || empty($metadata['height']) || !$url) {
            return false;
        }
        $tags->set_attribute('src', $url);
        $tags->remove_attribute('srcset');
        $tags->remove_attribute('sizes');
        $tags->remove_attribute('data-storychief-image-id');
        // Presentation classes and existing dimensions/styles remain untouched.
        foreach (preg_split('/\s+/', (string) $tags->get_attribute('class')) as $class) {
            if (preg_match('/^wp-image-\d+$/', $class)) {
                $tags->remove_class($class);
            }
        }
        $tags->add_class('wp-image-' . (int) $id);
        return true;
    }

    private function is_remote($url)
    {
        $host = ImageUploader::getHostUrl($url);
        return $host && $host !== ImageUploader::getHostUrl();
    }

    private function attachment($url, $alt, $post)
    {
        if (array_key_exists($url, $this->attachments)) {
            return $this->attachments[$url];
        }
        if (!$this->is_remote($url)) {
            $id = attachment_url_to_postid($url);
            if (!$id) {
                ImageUploader::report_error(new \WP_Error('storychief_attachment_not_found', 'The local source could not be mapped to an attachment.'), $url, $post->ID);
            }
            return $this->attachments[$url] = (int) $id;
        }
        $uploader = new ImageUploader($url, $alt, $post);
        return $this->attachments[$url] = $uploader->save() ? $uploader->attachment_id : 0;
    }
}
