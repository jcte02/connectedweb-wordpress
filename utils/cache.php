<?php
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
    wp_cache_add('ids', [], 'cweb');
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
