<?php
/**
 * @package Connected_Web
 * @version 1.1
 */
/*
Plugin Name: Connected Web
Plugin URI: https://connectedweb.org
Description: Connected Web feed plugin
Version: 1.1
Author: Fabio Endrizzi (jcte02)
Author URI: https://flashbeing.com
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Connected Wev is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Connected Web is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Connected Web.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once('utils/cache.php');

register_activation_hook(__FILE__, cweb_configure);
function cweb_configure()
{
    cweb_add_feed_type();
    flush_rewrite_rules();

    post_cache_init();

    // set default values
    if (!get_option('cweb_cacheable')) {
        update_option('cweb_cacheable', '1');
    }

    if (!get_option('cweb_expiresAfter')) {
        update_option('cweb_expiresAfter', 3600000);
    }
}


register_deactivation_hook(__FILE__, cwebfeed_deactivate);
function cweb_deactivate()
{
    flush_rewrite_rules();
}

register_uninstall_hook(__FILE__, cweb_purge);
function cweb_purge()
{
    // purge post cache
    post_cache_flush();

    // purge settings
    unregister_setting('cweb_settings', 'cweb_keywords');
    unregister_setting('cweb_settings', 'cweb_cacheable');
    unregister_setting('cweb_settings', 'cweb_expiresAfter');

    // purge configuration from database
    delete_option('cweb_keywords');
    delete_option('cweb_cacheable');
    delete_option('cweb_expiresAfter');

    die("not today");
}

add_action('init', cweb_add_feed_type);
function cweb_add_feed_type()
{
    add_feed('cweb', cweb_create_feed);
}

function cweb_create_feed()
{
    // load_template( plugins_url( "feed-cweb.php", __FILE__ ), false );
    load_template(dirname(__FILE__). "/feed-cweb.php");
}

add_action('admin_init', cweb_configure_settings);
function cweb_configure_settings()
{
    register_setting('cweb_settings', 'cweb_keywords');
    register_setting('cweb_settings', 'cweb_cacheable');
    register_setting('cweb_settings', 'cweb_expiresAfter');

    add_settings_section('cweb_feed_settings', 'Feed Settings', null, 'cweb');
    add_settings_section('cweb_cache_settings', 'Cache Settings', null, 'cweb');

    add_settings_field('cweb_keywords', 'Keywords', cweb_render_keywords, 'cweb', 'cweb_feed_settings', array('laber_for' => 'cweb_keywords'));
    
    add_settings_field('cweb_cacheable', 'Cacheable', cweb_render_cacheable, 'cweb', 'cweb_cache_settings', array('laber_for' => 'cweb_cacheable'));
    add_settings_field('cweb_expiresAfter', 'Expire time (in milliseconds)', cweb_render_expiresAfter, 'cweb', 'cweb_cache_settings', array('laber_for' => 'cweb_expiresAfter'));
}

add_action('admin_menu', cweb_add_settings_menu);
function cweb_add_settings_menu()
{
    add_options_page('Connected Web Settings', 'Connected Web', 'manage_options', 'cweb', cweb_render_menu);
}

function cweb_render_menu()
{
    if (!current_user_can('manage_options')) {
        return;
    } ?>
    <div class='wrap'>
        <h1><?= esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
        <?php
            settings_fields('cweb_settings');
    do_settings_sections('cweb');
    submit_button('Save Settings'); ?>
        </form>
    </div>
    <?php

}

function cweb_render_keywords()
{
    ?>
    <input type="text" name="cweb_keywords" value="<?php echo esc_attr(get_option('cweb_keywords')); ?>" />
    <?php

}

function cweb_render_cacheable()
{
    ?>
    <input type="hidden" name="cweb_cacheable" value="0" />
    <input type="checkbox" name="cweb_cacheable" value="1" <?php checked(get_option('cweb_cacheable'), '1') ?> />
    <?php

}

function cweb_render_expiresAfter()
{
    ?>
    <input type="number" name="cweb_expiresAfter" value="<?php echo esc_attr(get_option('cweb_expiresAfter')); ?>" min='60000' step='1000' required />
    <?php

}

add_action('show_user_profile', 'cweb_render_custom_user_fields');
add_action('edit_user_profile', 'cweb_render_custom_user_fields');
add_action("user_new_form", "cweb_render_custom_user_fields");
function cweb_render_custom_user_fields($user)
{
    $user_type = get_the_author_meta('user_type', $user->ID);
    $user_gender = get_the_author_meta('user_gender', $user->ID);
    $user_age = get_the_author_meta('user_age', $user->ID); ?>
	<h2><?php _e("Connected Web Informations", "blank"); ?></h3>
	<table class="form-table">
    <tr>
        <th>
            <label for="user_type"><?php _e("Author Type"); ?></label>
        </th>
        <td>
            <select name="user_type" id="user_type" style="width:180px">
            	<option value="">-Select Type-</option>
                <option value="company"   <?php selected($user_type, "company"); ?>>Company</option>
                <option value="person" <?php selected($user_type, "person"); ?>>Person</option>
                <option value="organization" <?php selected($user_type, "organization"); ?>>Organization</option>
                <option value="publisher" <?php selected($user_type, "publisher"); ?>>Publisher</option>
            </select>
        </td>
    </tr>
    <tr>
        <th>
            <label for="user_gender"><?php _e("Gender"); ?></label>
        </th>
        <td>
            <select name="user_gender" id="user_gender" style="width:180px">
            	<option value="">-Select Gender-</option>
                <option value="male"   <?php selected($user_gender, "male"); ?>>Male</option>
                <option value="female" <?php selected($user_gender, "female"); ?>>Female</option>
                <option value="other" <?php selected($user_gender, "other"); ?>>Other</option>
            </select>
        </td>
    </tr>
    <tr>
        <th>
            <label for="user_age"><?php _e("Your Age"); ?></label>
        </th>
        <td>
            <input type="number" name="user_age" id="age" size="60" min="0" value="<?php echo $user_age; ?>" />
        </td>
    </tr>
    </tr>
</table>
<?php 
}

add_action('personal_options_update', 'cweb_save_custom_user_fields');
add_action('edit_user_profile_update', 'cweb_save_custom_user_fields');
add_action('user_register', 'cweb_save_custom_user_fields');
function cweb_save_custom_user_fields($user_id)
{
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    update_user_meta($user_id, 'user_age', $_POST['user_age']);
    update_user_meta($user_id, 'user_type', $_POST['user_type']);
    update_user_meta($user_id, 'user_gender', $_POST['user_gender']);
}
