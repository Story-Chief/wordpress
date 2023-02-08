=== StoryChief ===
Contributors: StoryChief
Donate link: https://storychief.io
Tags: Content marketing calendar, social media scheduling, content marketing, schedule facebook posts, schedule to twitter, schedule posts to Linkedin, social media analytics
Requires at least: 4.6
Tested up to: 6.0
Requires PHP: 5.4
Stable tag: 1.0.37
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

All-in-one Content Marketing Workspace

== Description ==

Use StoryChief for collaboration on SEO blogposts, social posts and one-click multichannel distribution.

https://www.youtube.com/watch?v=cMYm0u07m9E

##### [Sign up for free](https://app.storychief.io/register)

=== Publish Better Content ===

Gain better collaboration, create more interactive and beautiful content with StoryChief and go Multi-Channel without any hurdles or technical requirements.

This plugin:

*   Publishes articles straight from StoryChief
*   Keeps your formatting like header tags, bold, links, lists etc
*   Does not alter your website's branding, by using your site's CSS for styling
*   Imports text and images from your StoryChief story
*   Supports multi-site
*   Supports SEO plugins natively: Yoast, SeoPress, All In One Seo Pack, RankMath
*   Supports custom fields with ACF through an extensions plugin
*   Supports multi-language with Polylang and WPML through an extensions plugin
*   Supports Divi theme and Builder

=== How It Works ===

1.  Register on [StoryChief](https://app.storychief.io/register)
2.  Add a WordPress channel
3.  Install and activate the plugin
4.  Configure the plugin by saving your encryption key
5.  Publish from StoryChief to your WordPress website


=== Requirements ===

* This plugin requires a [StoryChief](https://storychief.io) account.
	* Not a StoryChief user yet? [Sign up for free!](https://app.storychief.io/register)
* PHP version 5.4 or higher

=== Actions and filters ===

Developers: This plugin has numerous [actions and filters](https://codex.wordpress.org/Plugin_API) available that can be used to modify the default behaviour of the plugin.

Actions:
*   storychief_after_publish_action($payload)
*   storychief_after_delete_action($payload)
*   storychief_save_author_action($payload)
*   storychief_save_tags_action($payload)
*   storychief_save_categories_action($payload)
*   storychief_save_featured_image_action($payload)
*   storychief_save_seo_action($payload)
*   storychief_sideload_images_action($payload)

Filters:
*   storychief_before_handle_filter($payload)
*   storychief_is_draft_status($is_draft, $payload)
*   storychief_change_post_type($post_type, $payload)
*   storychief_alter_response($response)
*   storychief_publish_permalink($permalink, $postID)

== Installation ==

1.  Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2.  Activate the plugin through the 'Plugins' screen in WordPress
3.  Use the Settings -> StoryChief screen to configure the plugin
4.  Copy over your encryption key from StoryChief

== Frequently Asked Questions ==

Find our complete FAQ [here](https://help.storychief.io/en/?q=wordpress)
Support for [Divi Builder](https://help.storychief.io/en/articles/3223934-configure-wordpress-divi-builder)
Support for [WPBakery](https://help.storychief.io/en/articles/2111311-wordpress-how-to-configure-wpbakery)

== Screenshots ==

1.  Optimize your Blog Posts for SEO
2.  Manage Social Media
3.  Publish Anywhere
4.  Content Calendar
5.  Content Approval
6.  Revive Blog Posts
7.  Content Data Management
8.  Trigger Colleagues to reshare
9.  Measure performance
10.  Measure Quality

== Changelog ==

= 1.0.37 =
* Feature: Allow publishing to a page.
* Improvement: Improve how we upload featured image.

= 1.0.36 =
* Feature: Parse literal gutenberg blocks in the content

= 1.0.34 =
* Bugfix: Solved edge-case where media URL conflicts with post url

= 1.0.33 =
* Added formatting of shortcodes

= 1.0.32 =
* Tested up to WordPress 5.9

= 1.0.31 =
* Improvement: Add improvements to the security

= 1.0.30 =
* Improvement: Disable WP-Cron when StoryChief's webhook is called.

= 1.0.29 =
* Bugfix: New tags would not get connected to the post

= 1.0.28 =
* Feature: Added debug mode

= 1.0.27 =
* Bugfix: Don't duplicate og tags when RankMath is installed

= 1.0.26 =
* Improvement: Added storychief_alter_response filter

= 1.0.25 =
* Improvement: Tested up to WordPress 5.6
* Improvement: Added storychief_change_post_type filter
* Improvement: Updated filter and action documentation

= 1.0.23 =
* Improvement: Tested up to WordPress 5.5
* Bugfix: Add permission_callback on register_rest_route

= 1.0.23 =
* Bugfix: Fixed bug where saving the author would be done without sanitation disable

= 1.0.22 =
* Improvement: add storychief_is_draft_status filter for granular control over the publish status
* Improvement: move author linking to seperate action 'storychief_save_author_action'. Allows for granular control
* Improvement: add ability to customize the post type on configuration

= 1.0.21 =
* Improvement: remove image data check for duplicates, image optimizers change it each time.

= 1.0.20 =
* Improvement: added mapping of alt-tag

= 1.0.19 =
* Improvement: clear cache after new post is created or updated.

= 1.0.18 =
* Improvement: Improvement to image side-loading.

= 1.0.17 =
* Improvement: Try to map categories and tags on slug before name.

= 1.0.15 =
* Tested up to WordPress 5.4

= 1.0.15 =
* Improvement: added custom styling options for media alignment, video's and captions
* Improvement: tested up to WordPress 5.3
* Improvement: Renamed "Story Chief" and "Storychief" to the correct "StoryChief"

= 1.0.14 =
* Bugfix: Divi content formatting fix

= 1.0.13 =
* Improvement: Added support for Divi theme and Divi Builder

= 1.0.12 =
* Improvement: Add support for Rank Math
* Bugfix: Canonical URL

= 1.0.11 =
* Bugfix: SEO meta tags where displayed on non-singular pages when no SEO plugin was found.

= 1.0.10 =
* Bugfix: SEOPress pre_get_document_title filter was overwritten by StoryChief

= 1.0.9 =
* Improvement: Added support for All In One Seo Pack Pro

= 1.0.8 =
* Improvement: Added support for All In One Seo Pack
* Improvement: Added the article's title as the alt-tag for the Post Featured image

= 1.0.7 =
* Improvement: Added support for Yoast Premium
* Improvement: Tested up to WordPress 5.0

= 1.0.6 =
* Bugfix: refactored old filters to actions on handleUpdate webhook function

= 1.0.5 =
* Improvement: Return the post Pretty URl instead of Permalink as redirect modules are giving issues.

= 1.0.4 =
* Improvement: Suppress warnings and notices from other plugins that can get prepended or appended to the rest response on bad configured servers.

= 1.0.3 =
* Improvement: Better support for SeoPress and add fallback for when no SEO plugins are installed
* Bugfix: Images side-loaded for draft articles got in a subfolder based 01/01/1970 date instead op the post date.

= 1.0.2 =
* Bugfix: trigger side-loading of images on update function.

= 1.0.1 =
* Bugfix: use wp_check_filetype() instead of mime_content_type() as it is not always available

= 1.0.0 =
* Complete rewrite of plugin
* Feature: Option to side-load all images inside the content.
* Improvement: Avoid duplicate images.

= 0.4.2 =
* Bugfix: Permalink sometimes redirected bots (ex: Facebot) to the homepage. Changed the permalink to stop that.
* Improvement: Made notices less obtrusive.

= 0.4.0 =
* Added a filter to modify the returned permalink

= 0.3.9 =
* Clearer 'test mode' description in settings

= 0.3.8 =
* Added support for SeoPress

= 0.3.7 =
* Increased image side-loading timeout and handled exception gracefully

= 0.3.6 =
* Improved SEO integration

= 0.3.5 =
* Added settings to allow or disallow creation of new tags and categories

= 0.3.4 =
* Added support for SEO plugin Yoast
* Tested up to version 4.9

= 0.3.3 =
* Don't return a permalink in test mode, in order to avoid accidental social media posts in test mode

= 0.3.2 =
* Refactored for support of PHP 5.4

= 0.3.1 =
* Added handleUpdate function

= 0.3.0 =
* Added information messages for sub-plugins.
* Changed insert of tags, categories and featured image through filters (for overwrite/extension purposes)

= 0.2.9 =
* Reformatted code for reuse of import_image function

= 0.2.8 =
* Added custom filter: storychief_before_handle_filter
* Added custom action: storychief_after_publish_action
* Added custom action: storychief_after_delete_action
* Added custom action: storychief_after_test_action

= 0.2.7 =
* Added ability to sync multiple categories.
* Added an option to automatically create new authors

= 0.2.6 =
* Add ability to save as draft for testing purposes

= 0.2.5 =

= 0.2.4 =
* Added support for Google AMP

= 0.2.3 =
* Added support until PHP version 5.2.4

= 0.2.1 =
* Added WP installation url on configuration page for improved usability.
* Better error handling.

= 0.2 =
* Drop the REST API approach and reworked everything with one simple webhook.

= 0.1 =
* Connect to your StoryChief destination
* Publish your articles on your WordPress blog.

== Upgrade Notice ==

= 0.2 =
You will have to reconfigure your channel on WordPress and on StoryChief. It will take you less then a minute.

= 0.1 =
First version -- Deprecated --

