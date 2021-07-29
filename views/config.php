<?php
/** @var null|string $encryption_key */
/** @var null|string $wp_url */
/** @var array $post_types */
/** @var string $selected_post_type Is by default the value 'post' */
/** @var int $test_mode */
/** @var int $debug_mode */
/** @var int $author_create */
/** @var int $category_create */
/** @var int $tag_create */
/** @var int $sideload_images */
/** @var int $styling_align */
/** @var int $styling_caption */
/** @var int $styling_video */

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'config';

?>

<div class="wrap">
    <h1>StoryChief</h1>

    <p>
        <?php esc_html_e('To set up StoryChief, enter you encryption key given to you by StoryChief.', 'storychief'); ?>
    </p>

    <?php /*
    <h2 class="nav-tab-wrapper">
        <a href="?page=storychief" class="nav-tab <?php echo $active_tab === 'config' ? 'nav-tab-active' : '' ?>">
            <?php esc_html_e('Configuration', 'storychief'); ?>
        </a>
        <a href="?page=storychief&tab=styling"
           class="nav-tab <?php echo $active_tab === 'styling' ? 'nav-tab-active' : '' ?>">
            <?php esc_html_e('Styling', 'storychief'); ?>
        </a>
    </h2>
    */ ?>

    <form action="<?php echo esc_url(\Storychief\Admin::get_page_url()); ?>" method="post">
        <input type="hidden" name="tab" value="<?php echo esc_attr($active_tab); ?>" />
        <input type="hidden" name="action" value="enter-key">
        <?php wp_nonce_field(\Storychief\Admin::NONCE); ?>

        <?php if ($active_tab === 'config'): ?>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="key"><?php esc_html_e('Enter your StoryChief Key', 'storychief'); ?></label>
                    </th>
                    <td>
                        <input id="key" name="key" type="password" size="15" value="<?php echo esc_attr($encryption_key); ?>" class="regular-text">
                        <p class="description">
                            <?php esc_html_e('Your encryption key is given when you add a WordPress destination on StoryChief', 'storychief'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="key"><?php esc_html_e('Your WordPress url', 'storychief'); ?></label></th>
                    <td>
                        <input type="text" size="15" value="<?php echo esc_attr($wp_url); ?>" class="regular-text" readonly>
                        <p class="description"><?php esc_html_e('Save this in your StoryChief Configuration', 'storychief'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="sc_post_type"><?php esc_html_e('Select the post type', 'storychief'); ?></label>
                    </th>
                    <td>
                        <select name="sc_post_type">
                            <?php foreach ($post_types as $key => $value): ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php echo $key === $selected_post_type ? 'selected' : ''; ?>>
                                    <?php echo esc_html($value); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="test_mode"><?php esc_html_e('Testing mode', 'storychief'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="test_mode" value="1" <?php echo ($test_mode == 1) ? 'checked' : '' ?>> Enable test mode<br>
                        <p class="description">
                            <?php esc_html_e('Keep your articles as draft. This means you won\'t be able to publish multichannel as long as it\'s on', 'storychief'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="debug_mode"><?php esc_html_e('Debug mode', 'storychief'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="debug_mode" value="1" <?php echo ($debug_mode == 1) ? 'checked' : '' ?>> Enable debug mode<br>
                        <p class="description">
                            <?php esc_html_e('Logs any error in the file "/wp-content/plugins/story-chief/error.log". Use it for debugging or sharing with StoryChief support', 'storychief'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="author_create"><?php esc_html_e('Create unknown authors', 'storychief'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="author_create" value="1" <?php echo ($author_create == 1) ? 'checked' : '' ?>>
                        Enable creation of unknown authors<br>
                        <p class="description">
                            <?php esc_html_e('This option allows you to automatically create new authors in WordPress when needed.', 'storychief'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="category_create">
                            <?php esc_html_e('Create unknown categories', 'storychief'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="checkbox" name="category_create" value="1" <?php echo ($category_create == 1) ? 'checked' : '' ?>>
                        Enable creation of unknown categories<br>
                        <p class="description">
                            <?php esc_html_e('This option allows you to automatically create new categories in WordPress when needed.', 'storychief'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="tag_create">
                            <?php esc_html_e('Create unknown tags', 'storychief'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="checkbox" name="tag_create" value="1" <?php echo ($tag_create == 1) ? 'checked' : '' ?>>
                        Enable creation of unknown tags<br>
                        <p class="description">
                            <?php esc_html_e('This option allows you to automatically create new tags in WordPress when needed.', 'storychief'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="sideload_images">
                            <?php esc_html_e('Side-load images', 'storychief'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="checkbox" name="sideload_images" value="1" <?php echo ($sideload_images == 1) ? 'checked' : '' ?>>
                        Enable side-loading of images<br>
                        <p class="description">
                            <?php esc_html_e('All images inside an article will be downloaded to your WordPress installation.', 'storychief'); ?>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>

            <?php if (\Storychief\Compatibility\isDiviThemeActive()): ?>
                <hr />
                <h3>Divi Default Page Settings</h3>
                <p>We discovered you are using the Divi theme. You can configure the default settings for StoryChief
                    below</p>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label for="divi_page_layout"><?php esc_html_e('Page Layout', 'storychief'); ?></label>
                        </th>
                        <td>
                            <?php $page_layout = \Storychief\Settings\get_sc_option('divi_page_layout'); ?>
                            <select id="divi_page_layout" name="divi_page_layout">
                                <option value="et_right_sidebar" <?php echo $page_layout === 'et_right_sidebar' ? 'selected' : '' ?>>
                                    Right Sidebar
                                </option>
                                <option value="et_left_sidebar" <?php echo $page_layout === 'et_left_sidebar' ? 'selected' : '' ?>>
                                    Left Sidebar
                                </option>
                                <option value="et_no_sidebar" <?php echo $page_layout === 'et_no_sidebar' ? 'selected' : '' ?>>
                                    No Sidebar
                                </option>
                                <option value="et_full_width_page" <?php echo $page_layout === 'et_full_width_page' ? 'selected' : '' ?>>
                                    Fullwidth
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="divi_hide_nav"><?php esc_html_e('Hide Nav Before Scroll',
                                                                        'storychief'); ?></label>
                        </th>
                        <td>
                            <?php $hide_nav = \Storychief\Settings\get_sc_option('divi_hide_navigation'); ?>
                            <select id="divi_hide_navigation" name="divi_hide_navigation">
                                <option value="default" <?php echo $hide_nav === 'default' ? 'selected' : '' ?>>
                                    Default
                                </option>
                                <option value="no" <?php echo $hide_nav === 'no' ? 'selected' : '' ?>>Off</option>
                                <option value="on" <?php echo $hide_nav === 'on' ? 'selected' : '' ?>>On</option>
                            </select>
                        </td>
                    </tr>
                    </tbody>
                </table>
            <?php endif ?>

            <?php if (\Storychief\Compatibility\isDiviBuilderActive()): ?>
                <hr />
                <h3>Divi Builder Default Post Settings</h3>
                <p>We discovered you are using the Divi builder for Posts. You can configure the default settings for
                    StoryChief below</p>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label for="divi_dot_navigation"><?php esc_html_e('Dot Navigation',
                                                                              'storychief'); ?></label>
                        </th>
                        <td>
                            <?php $dot_nav = \Storychief\Settings\get_sc_option('divi_dot_navigation'); ?>
                            <select id="divi_dot_navigation" name="divi_dot_navigation">
                                <option value="off" <?php echo $dot_nav === 'off' ? 'selected' : '' ?>>Off</option>
                                <option value="on" <?php echo $dot_nav === 'on' ? 'selected' : '' ?>>On</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="divi_show_title"><?php esc_html_e('Post Title', 'storychief'); ?></label>
                        </th>
                        <td>
                            <?php $show_title = \Storychief\Settings\get_sc_option('divi_show_title'); ?>
                            <select id="divi_show_title" name="divi_show_title">
                                <option value="on" <?php echo $show_title === 'on' ? 'selected' : '' ?>>Show
                                </option>
                                <option value="off" <?php echo $show_title === 'off' ? 'selected' : '' ?>>Hide
                                </option>
                            </select>
                        </td>
                    </tr>
                    </tbody>
                </table>
            <?php endif ?>
        <?php endif; ?>

        <?php if ($active_tab === 'styling'): ?>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="styling_align">
                            <?php esc_html_e('Media align', 'storychief'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="checkbox"
                               name="styling_align"
                               value="1" <?php echo ($styling_align == 1) ? 'checked' : '' ?>
                        /> Align media left or right. <br />
                        <p class="description">
                            <?php esc_html_e('Adds default styles for aligning media (images, video\'s etc) left or right',
                                             'storychief'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="styling_align">
                            <?php esc_html_e('Captions', 'storychief'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="checkbox"
                               name="styling_caption"
                               value="1" <?php echo ($styling_caption == 1) ? 'checked' : '' ?>
                        /> Media captions. <br />
                        <p class="description">
                            <?php esc_html_e('Adds default styles for captions', 'storychief'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="styling_video">
                            <?php esc_html_e('Video\'s', 'storychief'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="checkbox"
                               name="styling_video"
                               value="1" <?php echo ($styling_video == 1) ? 'checked' : '' ?>
                        /> Video blocks. <br />
                        <p class="description">
                            <?php esc_html_e('Adds default styles for video\'s', 'storychief'); ?>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
        <?php endif; ?>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary"
                   value="<?php esc_attr_e('Save changes', 'storychief'); ?>">
        </p>
    </form>
</div>
