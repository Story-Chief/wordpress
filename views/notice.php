<?php if ( $type == 'parent-plugin' ) :?>
    <div id="storychief-warning" class="notice notice-warning is-dismissible">
        <p>
            <strong>
                <?php
                /* translators: Plugin version number */
                printf( esc_html__('StoryChief %s requires WP Rest API 2.0 or higher.', 'story-chief'), esc_html(STORYCHIEF_VERSION));
                ?>
            </strong>
            <?php
            /* translators: Plugin url */
            printf(__('Please <a href="%1$s">install WordPress REST API</a>.', 'story-chief'), esc_url('https://wordpress.org/plugins/rest-api/'));
            ?>
        </p>
    </div>
<?php elseif ( $type == 'wpml-plugin' ) :?>
    <div id="storychief-warning" class="notice notice-info is-dismissible">
        <p>
            <strong><?php printf( esc_html__('There is an extension available for WPML', 'story-chief'));?></strong>
            <?php
            /* translators: Plugin url */
            printf(__('Download it <a href="%1$s">here</a>.', 'story-chief'), esc_url('https://wordpress.org/plugins/story-chief-wpml/'));
            ?>
        </p>
    </div>
<?php elseif ( $type == 'polylang-plugin' ) :?>
    <div id="storychief-warning" class="notice notice-info is-dismissible">
        <p>
            <strong><?php printf( esc_html__('There is an extension available for Polylang', 'story-chief'));?></strong>
            <?php
            /* translators: Plugin url */
            printf(__('Download it <a href="%1$s">here</a>.', 'story-chief'), esc_url('https://wordpress.org/plugins/story-chief-polylang/'));
            ?>
        </p>
    </div>
<?php elseif ( $type == 'acf-plugin' ) :?>
    <div id="storychief-warning" class="notice notice-info is-dismissible">
        <p>
            <strong><?php printf( esc_html__('There is an extension available for ACF', 'story-chief'));?></strong>
            <?php
            /* translators: Plugin url */
            printf(__('Download it <a href="%1$s">here</a>.', 'story-chief'), esc_url('https://wordpress.org/plugins/storychief-acf/'));
            ?>
        </p>
    </div>
<?php elseif ( $type == 'version' ) :?>
    <div id="storychief-warning" class="notice notice-warning is-dismissible">
        <p>
            <strong>
                <?php
                /* translators: Plugin version */
                printf( esc_html__('StoryChief %s requires WordPress 4.6 or higher.', 'story-chief'), esc_html(STORYCHIEF_VERSION));
                ?>
            </strong>
            <?php
            /* translators: url to WordPress docs */
            printf(__('Please <a href="%1$s">upgrade WordPress</a> to a current version.', 'story-chief'), esc_url('http://codex.wordpress.org/Upgrading_WordPress'));
            ?>
        </p>
    </div>
<?php elseif( $type == 'config-set') : ?>
    <div id="storychief-warning" class="notice notice-success is-dismissible">
        <p>
            <strong><?php printf( esc_html__('Configuration saved', 'story-chief'));?></strong>
        </p>
    </div>
<?php elseif( $type == 'undefined') : ?>
    <div id="storychief-warning" class="notice notice-error is-dismissible">
        <p>
            <strong><?php printf( esc_html__('An unknown error occurred', 'story-chief'));?></strong>
        </p>
    </div>
<?php endif; ?>
