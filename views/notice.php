<?php if ( $type == 'parent-plugin' ) :?>
    <div id="storychief-warning" class="notice notice-warning is-dismissible">
        <p>
            <strong>
                <?php
                /* translators: Plugin version number */
                printf( esc_html__('StoryChief %s requires WP Rest API 2.0 or higher.', 'story-chief'), esc_html(STORYCHIEF_VERSION));
                ?>
            </strong>
            <?php printf(esc_html__('Please install WordPress REST API.', 'story-chief')); ?>
            <a href="https://wordpress.org/plugins/rest-api/"><?php printf(esc_html__('Download', 'story-chief')); ?></a>
        </p>
    </div>
<?php elseif ( $type == 'wpml-plugin' ) :?>
    <div id="storychief-warning" class="notice notice-info is-dismissible">
        <p>
            <strong><?php printf( esc_html__('There is an extension available for WPML', 'story-chief'));?></strong>
            <a href="https://wordpress.org/plugins/story-chief-wpml/"><?php printf(esc_html__('Download', 'story-chief')); ?></a>
        </p>
    </div>
<?php elseif ( $type == 'polylang-plugin' ) :?>
    <div id="storychief-warning" class="notice notice-info is-dismissible">
        <p>
            <strong><?php printf( esc_html__('There is an extension available for Polylang', 'story-chief'));?></strong>
            <a href="https://wordpress.org/plugins/story-chief-polylang/"><?php printf(esc_html__('Download', 'story-chief')); ?></a>
        </p>
    </div>
<?php elseif ( $type == 'acf-plugin' ) :?>
    <div id="storychief-warning" class="notice notice-info is-dismissible">
        <p>
            <strong><?php printf( esc_html__('There is an extension available for ACF', 'story-chief'));?></strong>
            <a href="https://wordpress.org/plugins/storychief-acf/"><?php printf(esc_html__('Download', 'story-chief')); ?></a>
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
            <a href="http://codex.wordpress.org/Upgrading_WordPress"><?php printf(esc_html__('Upgrade', 'story-chief')); ?></a>
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
