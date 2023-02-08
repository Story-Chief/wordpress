<?php

namespace Storychief;

class Admin {
	const NONCE = 'storychief-update-key';

	private static $initiated = false;
	private static $notices = array();

	public static function init() {
		if (!self::$initiated) {
            self::$initiated = true;
            global $pagenow;

            add_action('admin_init', array('\Storychief\Admin', 'admin_init'));
            add_action('admin_menu', array('\Storychief\Admin', 'admin_menu'));

            if ($pagenow === 'options-general.php' && isset($_GET['page']) && $_GET['page'] === 'storychief') {
                add_action('admin_notices', array('\Storychief\Admin', 'admin_notice'));
            }

            add_filter('plugin_action_links', array('\Storychief\Admin', 'plugin_action_links'), 10, 2);
		}

		if (isset($_POST['action']) && $_POST['action'] == 'enter-key') {
			self::save_configuration();
		}
	}

	public static function admin_init() {
		load_plugin_textdomain('storychief');
		if(class_exists('Polylang') && !class_exists('Storychief_PPL')){
			self::notice_polylang_plugin_available();
		}

		if(function_exists('icl_object_id') && !class_exists('Storychief_WPML')){
			self::notice_wpml_plugin_available();
		}

		if(class_exists('Acf') && !class_exists('Storychief_ACF')){
			self::notice_acf_plugin_available();
		}
	}

	public static function admin_menu() {
		$hook = add_options_page('StoryChief', 'StoryChief', 'manage_options', 'storychief', array(
			'\Storychief\Admin',
			'display_configuration_page'
		));

		add_action("load-$hook", array('\Storychief\Admin', 'admin_help'));
	}

	public static function admin_help() {
		$current_screen = get_current_screen();
		// Screen Content
		if (current_user_can('manage_options')) {
			$current_screen->add_help_tab(
				array(
					'id'      => 'overview',
					'title'   => __('Overview', 'storychief'),
					'content' =>
						'<p><strong>' . esc_html__('StoryChief Configuration', 'storychief') . '</strong></p>' .
						'<p>' . esc_html__('StoryChief publishes posts, so you can focus on more important things.', 'storychief') . '</p>' .
						'<p>' . esc_html__('Save your given key here.', 'storychief') . '</p>',
				)
			);

			$current_screen->add_help_tab(
				array(
					'id'      => 'settings',
					'title'   => __('Settings', 'storychief'),
					'content' =>
						'<p><strong>' . esc_html__('StoryChief Configuration', 'storychief') . '</strong></p>' .
						'<p><strong>' . esc_html__('Encryption Key', 'storychief') . '</strong> - ' . esc_html__('Enter your Encryption key.', 'storychief') . '</p>',
				)
			);
		}

		// Help Sidebar
		$current_screen->set_help_sidebar(
			'<p><strong>' . esc_html__('For more information:', 'storychief') . '</strong></p>' .
			'<p><a href="https://help.storychief.io/faq" target="_blank">' . esc_html__('StoryChief FAQ', 'storychief') . '</a></p>' .
			'<p><a href="https://help.storychief.io" target="_blank">' . esc_html__('StoryChief Support', 'storychief') . '</a></p>'
		);
	}

	public static function save_configuration() {
		if (function_exists('current_user_can') && !current_user_can('manage_options')) {
			die(__('Cheatin&#8217; uh?', 'storychief'));
		}
		if (!wp_verify_nonce($_POST['_wpnonce'], self::NONCE)) {
			return false;
		}

        if($_POST['tab'] === 'config') {
            \Storychief\Settings\update_sc_option('encryption_key', $_POST['key']);
            \Storychief\Settings\update_sc_option('test_mode', isset($_POST['test_mode']) ? true : false);
            \Storychief\Settings\update_sc_option('debug_mode', isset($_POST['debug_mode']) ? true : false);
            \Storychief\Settings\update_sc_option('author_create', isset($_POST['author_create']) ? true : false);
            \Storychief\Settings\update_sc_option('category_create', isset($_POST['category_create']) ? true : false);
            \Storychief\Settings\update_sc_option('tag_create', isset($_POST['tag_create']) ? true : false);
            \Storychief\Settings\update_sc_option('sideload_images', isset($_POST['sideload_images']) ? true : false);

            if(isset($_POST['divi_page_layout'])){
                \Storychief\Settings\update_sc_option('divi_page_layout', $_POST['divi_page_layout']);
            }
            if(isset($_POST['divi_dot_navigation'])){
                \Storychief\Settings\update_sc_option('divi_dot_navigation', $_POST['divi_dot_navigation']);
            }
            if(isset($_POST['divi_hide_navigation'])){
                \Storychief\Settings\update_sc_option('divi_hide_navigation', $_POST['divi_hide_navigation']);
            }
            if(isset($_POST['divi_show_title'])){
                \Storychief\Settings\update_sc_option('divi_show_title', $_POST['divi_show_title']);
            }
            if(isset($_POST['sc_post_type'])){
                \Storychief\Settings\update_sc_option('post_type', $_POST['sc_post_type']);
            }
        } elseif ($_POST['tab'] === 'styling') {
            \Storychief\Settings\update_sc_option('styling_align', isset($_POST['styling_align']) ? true : false);
            \Storychief\Settings\update_sc_option('styling_caption', isset($_POST['styling_caption']) ? true : false);
            \Storychief\Settings\update_sc_option('styling_video', isset($_POST['styling_video']) ? true : false);
        }

		self::notice_config_saved();

		return true;
	}

	public static function settings_link($links) {
		$settings_link = '<a href="options-general.php?page=storychief">' . __('Settings') . '</a>';
		array_push($links, $settings_link);

		return $links;
	}

	public static function plugin_action_links($links, $file) {
		if ($file == plugin_basename(plugin_dir_url(__FILE__) . '/storychief.php')) {
			$links[] = '<a href="' . esc_url(self::get_page_url()) . '">' . esc_html__('Settings', 'storychief') . '</a>';
		}

		return $links;
	}

	public static function get_page_url() {
		$args = array('page' => 'storychief');
		$url = add_query_arg($args, admin_url('options-general.php'));

		return $url;
	}

	public static function display_configuration_page() {
		$encryption_key = \Storychief\Settings\get_sc_option('encryption_key');
		$test_mode = \Storychief\Settings\get_sc_option('test_mode');
		$debug_mode = \Storychief\Settings\get_sc_option('debug_mode');
		$author_create = \Storychief\Settings\get_sc_option('author_create');
        $category_create = \Storychief\Settings\get_sc_option('category_create');
        $tag_create = \Storychief\Settings\get_sc_option('tag_create');
        $sideload_images = \Storychief\Settings\get_sc_option('sideload_images');

        $styling_caption = \Storychief\Settings\get_sc_option('styling_caption');
        $styling_video = \Storychief\Settings\get_sc_option('styling_video');
        $styling_align = \Storychief\Settings\get_sc_option('styling_align');

        $post_types = get_post_types(['_builtin' => false]);
        $post_types['post'] = 'post';
        $post_types['page'] = 'page';
        $selected_post_type = \Storychief\Settings\get_sc_option('post_type') ? \Storychief\Settings\get_sc_option('post_type') : 'post';
        $wp_url = get_site_url();
        self::view('config', compact('encryption_key', 'wp_url', 'test_mode', 'debug_mode', 'author_create', 'category_create', 'tag_create', 'sideload_images', 'styling_caption', 'styling_video', 'styling_align', 'post_types', 'selected_post_type'));
	}

    public static function view($name, array $args = array()) {
        $args = apply_filters('storychief_view_arguments', $args, $name);
        foreach ($args AS $key => $val) {
            $$key = $val;
        }

        load_plugin_textdomain('storychief');
        $file = STORYCHIEF_DIR . '/views/' . $name . '.php';
        include($file);
    }

	/*----------- NOTICES -----------*/
	public static function admin_notice() {
		if (!empty(self::$notices)) {
			foreach (self::$notices as $notice) {
                self::view('notice', $notice);
			}

			self::$notices = array();
		}
	}

	public static function notice_undefined_error() {
		self::$notices[] = array(
			'type' => 'undefined',
		);
	}

	public static function notice_invalid_version() {
		self::$notices[] = array(
			'type' => 'version',
		);
	}

	public static function notice_parent_plugin_required() {
		self::$notices[] = array(
			'type' => 'parent-plugin',
		);
	}

	public static function notice_wpml_plugin_available() {
		self::$notices[] = array(
			'type' => 'wpml-plugin',
		);
	}

	public static function notice_polylang_plugin_available() {
		self::$notices[] = array(
			'type' => 'polylang-plugin',
		);
	}

	public static function notice_acf_plugin_available() {
		self::$notices[] = array(
			'type' => 'acf-plugin',
		);
	}

	public static function notice_config_saved() {
		self::$notices[] = array(
			'type' => 'config-set',
		);
	}
}
