<?php
/*
connectedweb
Copyright (C) 2017  Fabio Endrizzi (jcte02)

This file is part of connectedweb.

connectedweb is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

connectedweb is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with connectedweb.  If not, see <http://www.gnu.org/licenses/>.
*/

defined('ABSPATH') or die('OwO');

function onoff_switch($option, $toggle)
{
    ?>
    <input type="hidden" name="<?php echo esc_attr($option) ?>" value="0" />
    <div class="onoffswitch">
        <input type="checkbox" name="<?php echo esc_attr($option) ?>" id="<?php echo esc_attr($option) ?>" 
            class="onoffswitch-checkbox" value="1" <?php checked(get_option($option), '1') ?>
            onchange="
                jQuery('.<?php echo esc_attr($toggle) ?>').toggle();
                if(jQuery('#<?php echo esc_attr($toggle) ?>').is(':visible')) 
                    jQuery('#<?php echo esc_attr($toggle) ?>').prop('required', 'true');
                else
                    jQuery('#<?php echo esc_attr($toggle) ?>').removeAttr('required');" />
        <label class="onoffswitch-label" for="<?php echo esc_attr($option) ?>">
            <span class="onoffswitch-inner"></span>
            <span class="onoffswitch-switch"></span>
        </label>
    </div>
    <?php

}

function hidden($on)
{
    if ($on) {
        return 'hidden';
    }
}

function required($on)
{
    if (!$on) {
        echo 'required';
    }
}
