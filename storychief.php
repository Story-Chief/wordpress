<?php

/**
 * Plugin Name: StoryChief
 * Plugin URI: http://storychief.io/wordpress
 * Description: Publish your blog posts from StoryChief to WordPress.
 * Version: 1.0.41
 * Requires at least: 5.2
 * Requires PHP: 7.0
 * Author: StoryChief
 * Text Domain: storychief
 * Author URI: http://storychief.io
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Storychief;

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define('STORYCHIEF_VERSION', '1.0.41');
if (!defined('STORYCHIEF_DIR')) {
	define('STORYCHIEF_DIR', __DIR__);
}

require_once(STORYCHIEF_DIR . '/includes/async-tasks.php');
require_once(STORYCHIEF_DIR . '/includes/formatting.php');
require_once(STORYCHIEF_DIR . '/includes/settings.php');
require_once(STORYCHIEF_DIR . '/includes/tools.php');
require_once(STORYCHIEF_DIR . '/includes/compatibility.php');
require_once(STORYCHIEF_DIR . '/includes/install.php');
require_once(STORYCHIEF_DIR . '/includes/webhook.php');
require_once(STORYCHIEF_DIR . '/includes/mapping.php');
require_once(STORYCHIEF_DIR . '/includes/class.imageuploader.php');

if (is_admin()) {
	require_once(STORYCHIEF_DIR . '/includes/class.admin.php');
	add_action('init', array('\Storychief\Admin', 'init'));
	add_filter('plugin_action_links_' . plugin_basename(__FILE__), array('\Storychief\Admin', 'settings_link'));
}
