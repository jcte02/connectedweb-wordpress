<?php
/**
 * @package ConnectedWeb
 * @version dev
 */
/*
Plugin Name: ConnectedWeb
Plugin URI: https://connectedweb.org
Description: Add Connected Web functionality to your Wordpress blog.
Version: dev
Author: Fabio Endrizzi (jcte02)
Author URI: https://flashbeing.com
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

ConnectedWeb is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Connected Web is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ConnectedWeb.  If not, see <http://www.gnu.org/licenses/>.
*/

defined('ABSPATH') or die('OwO');

add_action('wp_head', function () {
    ?>
    <link rel="alternate" type="application/connectedweb+jsonp" 
    title="<?php echo get_bloginfo('name') ?>" href="<?php echo get_feed_link('connectedweb') ?>" />
    <?php

});

add_action('init', 'connectedweb_register_feed');
function connectedweb_register_feed()
{
    add_feed('connectedweb', function () {
        load_template(plugin_dir_path(__FILE__) . "/connectedweb-feed.php");
    });

    add_feed('connectedweb/source', function () {
        load_template(plugin_dir_path(__FILE__) . "/connectedweb-source.php");
    });
}

register_activation_hook(__FILE__, function () {
    connectedweb_register_feed();
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function () {
    unregister_setting('connectedweb', 'blog_keywords');
    unregister_setting('connectedweb', 'can_cache');
    unregister_setting('connectedweb', 'cache_expire_milliseconds');
    
    flush_rewrite_rules();
});

register_uninstall_hook(__FILE__, 'connectedweb_uninstall');
function connectedweb_uninstall()
{
    delete_option('blog_keywords');
    delete_option('can_cache');
    delete_option('cache_expire_milliseconds');
}

add_action('admin_init', function () {
    register_setting('connectedweb', 'blog_keywords', array(
        'type' => 'string',
        'description' => 'Blog keywords (separated by comma)',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => ''
    ));

    register_setting('connectedweb', 'can_cache', array(
        'type' => 'int',
        'description' => 'Whether or not the feed can be cached by readers',
        'sanitize_callback' => 'intval',
        'default' => 1
    ));
    
    register_setting('connectedweb', 'cache_expire_milliseconds', array(
        'type' => 'int',
        'description' => 'Expire time of the cache (in milliseconds)',
        'sanitize_callback' => 'intval',
        'default' => 3600000
    ));

    add_settings_section('connectedweb-feed', 'Feed Settings', function () {
    }, 'connectedweb');
    
    add_settings_section('connectedweb-cache', 'Cache Settings', function () {
    }, 'connectedweb');

    add_settings_field('blog_keywords', 'Blog keywords (separated by comma)', function () {
        ?>
        <input type="text" name="blog_keywords" value="<?php echo esc_attr(get_option('blog_keywords')); ?>" />
        <?php

    }, 'connectedweb', 'connectedweb-feed', array('laber_for' => 'blog_keywords'));
    
    add_settings_field('can_cache', 'Readers can cache feed', function () {
        ?>
        <style>
        <?php include('admin/css/slider-round.css') ?>
        </style>
        <input type="hidden" name="can_cache" value="0" />
        <label class="switch">
            <input type="checkbox" name="can_cache" value="1" <?php checked(get_option('can_cache'), '1') ?> />
            <div class="slider round" />
        </label>
        <?php

    }, 'connectedweb', 'connectedweb-cache', array('laber_for' => 'can_cache'));
    
    add_settings_field('cache_expire_milliseconds', 'Expire time of the cached feed (in milliseconds)', function () {
        ?>
        <input type="number" name="cache_expire_milliseconds" value="<?php echo esc_attr(get_option('cache_expire_milliseconds')); ?>" min='60000' step='1000' required />
        <?php

    }, 'connectedweb', 'connectedweb-cache', array('laber_for' => 'cache_expire_milliseconds'));
});

add_action('admin_menu', function () {
    add_options_page('Connected Web Settings', 'Connected Web', 'manage_options', 'connecteweb', function () {
        if (!current_user_can('manage_options')) {
            return;
        } ?>
        <div class='wrap'>
            <h1><?php esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
        <?php
        settings_fields('connectedweb');
        do_settings_sections('connectedweb');
        submit_button('Save Settings'); ?>
            </form>
        </div>
        <?php

    });
});

require_once('includes/metadata/user.php');
require_once('includes/metadata/attachment.php');
