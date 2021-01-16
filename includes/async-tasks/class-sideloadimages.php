<?php
/**
 * Events that should cause Storychief to sideload all images inside body.
 *
 * @package Storychief
 */

namespace Storychief\AsyncTasks;

/**
 * Async task for "storychief_sideload_images".
 *
 * @link https://github.com/techcrunch/wp-async-task
 */
class SideloadImages extends \WP_Async_Task {

    /**
     * The action that normally would have been called.
     *
     * @var string
     */
    public $action = 'storychief_sideload_images_action';

    /**
     * Prepare data for the asynchronous request.
     *
     * As nothing needs to be prepared, this method simply returns an empty array.
     *
     * @param array $data An array of arguments sent to the hook.
     * @return array An empty array, as there are no arguments.
     */
    protected function prepare_data($data) {
        $post_id = $data[0];
        return array('post_id' => $post_id);
    }

    /**
     * Run the async task action.
     */
    protected function run_action() {
        if (\Storychief\Settings\get_sc_option('sideload_images')) {
            $post_id = $_POST['post_id'];
            $post = get_post($post_id);
            if ($post) {
                $hook = 'wp_async_storychief_sideload_images_action';
                do_action($hook, $post);
            }
        }
    }
}
