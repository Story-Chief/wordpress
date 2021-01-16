<?php

namespace Storychief\Settings;

/**
 * Fetch a StoryChief setting key
 *
 * @param $key
 * @return mixed|void
 */
function get_sc_option($key) {
    return get_option('storychief_'.$key);
}

/**
 * Upsert a StoryChief setting key
 *
 * @param $key
 * @param $value
 */
function update_sc_option($key, $value) {
    update_option('storychief_'.$key, $value);
}

/**
 * Delete a StoryChief setting key
 *
 * @param $key
 */
function delete_sc_option($key) {
    delete_option('storychief_'.$key);
}
