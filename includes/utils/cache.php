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

class cache_entry
{
    public $lastwrite;
    public $data;

    public function __construct($lastwrite, $data)
    {
        $this->lastwrite = $lastwrite;
        $this->data = $data;
    }
}

function post_cache_set($id, $lastwrite, $data)
{
    _post_cache_track_id($id);
    wp_cache_set($id, new cache_entry($lastwrite, $data), 'cweb');
}

function post_cache_get($id, $lastwrite)
{
    $obj = wp_cache_get($id, 'cweb');

    if ($obj->lastwrite == $lastwrite) {
        return $obj->data;
    }

    return false;
}

function post_cache_init()
{
    if (!wp_cache_get('ids', 'cweb')) {
        wp_cache_set('ids', array(), 'cweb');
    }
}

function _post_cache_track_id($id)
{
    $ids = wp_cache_get('ids', 'cweb');

    if (!in_array($id, $ids)) {
        $ids[] = $id;
    }

    wp_cache_set('ids', $ids, 'cweb');
}

function post_cache_flush()
{
    $ids = wp_cache_get('ids', 'cweb');

    foreach ($ids as $id) {
        wp_cache_delete($id, 'cweb');
    }

    wp_cache_delete('ids', cweb);
}
