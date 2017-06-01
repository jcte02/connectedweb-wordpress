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
    unregister_setting('connectedweb', 'use_popular_tags');
    unregister_setting('connectedweb', 'custom_keywords');
    unregister_setting('connectedweb', 'use_default_cache');
    unregister_setting('connectedweb', 'can_cache');
    unregister_setting('connectedweb', 'cache_expire_milliseconds');
    
    flush_rewrite_rules();
});

register_uninstall_hook(__FILE__, 'connectedweb_uninstall');
function connectedweb_uninstall()
{
    delete_option('use_popular_tags');
    delete_option('custom_keywords');
    delete_option('use_default_cache');
    delete_option('can_cache');
    delete_option('cache_expire_milliseconds');
}

add_action('admin_enqueue_scripts', function () {
    wp_enqueue_script('jquery-core');
    // wp_enqueue_script('jquery-effects-core');
    // wp_enqueue_script('jquery-effects-slide');
    wp_enqueue_style('onoff', plugins_url('admin/css/onoff.css', __FILE__));
});

require_once('includes/output/onoff.php');

add_action('admin_init', function () {
    register_setting('connectedweb', 'use_popular_tags', array(
        'type' => 'int',
        'description' => 'Wheter to use popular tags as feed keywords',
        'sanitize_callback' => 'intval',
        'default' => 1
    ));

    register_setting('connectedweb', 'custom_keywords', array(
        'type' => 'string',
        'description' => 'Custom keywords (separated by comma)',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => ''
    ));

    register_setting('connectedweb', 'use_default_cache', array(
        'type' => 'int',
        'description' => 'Wheter to use default cache settings',
        'sanitize_callback' => 'intval',
        'default' => 1
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

    add_settings_field('use_popular_tags', 'Use popular tags as keywords', function () {
        onoff_switch('use_popular_tags', 'custom_keywords');
    }, 'connectedweb', 'connectedweb-feed', array(
        'label_for' => 'use_popular_tags'
    ));

    add_settings_field('custom_keywords', 'Custom keywords (separated by comma)', function () {
        ?>
        <input type="text" name="custom_keywords" id="custom_keywords"
                pattern="(\w{3,},?\s?){1,5}" 
                value="<?php echo esc_attr(get_option('custom_keywords')); ?>" 
                <?php required(get_option('use_popular_tags', 1)) ?> />
        <?php

    }, 'connectedweb', 'connectedweb-feed', array(
        'label_for' => 'custom_keywords',
        'class' => 'custom_keywords ' . hidden(get_option('use_popular_tags', 1))
    ));
    
    add_settings_field('use_default_cache', 'Use default cache settings', function () {
        onoff_switch('use_default_cache', 'cache_settings');
    }, 'connectedweb', 'connectedweb-cache', array(
        'label_for' => 'use_default_cache'
    ));

    add_settings_field('can_cache', 'Readers can cache feed', function () {
        onoff_switch('can_cache', 'null');
    }, 'connectedweb', 'connectedweb-cache', array(
        'label_for' => 'can_cache',
        'class' => 'cache_settings ' . hidden(get_option('use_default_cache', 1))
    ));
    
    add_settings_field('cache_expire_milliseconds', 'Expire time of the cached feed (in milliseconds)', function () {
        ?>
        <input type="number" name="cache_expire_milliseconds" 
                min='60000' step='1000'
                required value="<?php echo esc_attr(get_option('cache_expire_milliseconds')); ?>" />
        <?php

    }, 'connectedweb', 'connectedweb-cache', array(
        'label_for' => 'cache_expire_milliseconds',
        'class' => 'cache_settings ' . hidden(get_option('use_default_cache', 1))
    ));
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
