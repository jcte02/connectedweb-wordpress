<?php
/*
This file is part of ConnectedWeb

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
            <input type="number" name="user_age" id="age" size="60" min="0" value="<?php echo esc_attr($user_age); ?>" />
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
    
    update_user_meta($user_id, 'user_age', intval($_POST['user_age']));
    update_user_meta($user_id, 'user_type', sanitize_text_field($_POST['user_type']));
    update_user_meta($user_id, 'user_gender', sanitize_text_field($_POST['user_gender']));
}
