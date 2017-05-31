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

function filter($obj)
{
    $obj = array_map(function ($child) {
        if (is_array($child)) {
            return filter($child);
        } else {
            return $child;
        }
    }, (array) $obj);
    
    return array_filter((array) $obj, function ($value) {
        return !empty($value);
    });
}

function encode($obj)
{
    return json_encode(filter($obj), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
