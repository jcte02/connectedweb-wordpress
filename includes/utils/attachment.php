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

function get_author($id = false)
{
    // get_userdata($id);
    return (object)array(
        'name' => get_the_author_meta('display_name', $id),
        'type' => get_the_author_meta('user_type', $id),
        'url' => get_the_author_meta('url', $id),
        'age' => intval(get_the_author_meta('user_age', $id)),
        'gender' => get_the_author_meta('user_gender', $id)
    );
}

function get_attachment($id, $callback = false, $not = array())
{
    $post = get_post($id);
    $lastmodified = $post->post_modified_gmt;

    // $cache = post_cache_get($id, $lastmodified);

    // if (empty($cache)) {
    $data = array(
        // 'author' => get_author($post->post_author),
        'url' => wp_get_attachment_url($post->ID),
        'title' => $post->post_title,
        'caption' => $post->post_excerpt,
        'description' => $post->post_content,
        'type' => $post->post_mime_type
    );
    
    foreach ($not as $key) {
        unset($data[$key]);
    }
    
    if (is_callable($callback)) {
        $callback($data, wp_get_attachment_metadata($id), get_attached_file($id));
    }
    
    // post_cache_set($id, $lastmodified, $data);
    return $data;
    // } else {
    //     return $cache;
    // }
}

function get_text($value, $callback = false)
{
    $data = array(
        'value' => $value,
        'appearance' => '',
    );
    
    if (is_callable($callback)) {
        $callback($data);
    }

    return (object)array(
        'type' => 'text',
        'data' => $data
    );
}

function get_image_object($id)
{
    return get_attachment($id, function (&$data, $metadata, $dir) {
        $data['size'] = filesize($dir);
        $data['width'] = $metadata['width'];
        $data['height'] = $metadata['height'];
    
        $data['resolutions'] = array();

        $basedir = dirname($dir);
        $baseurl = dirname($data['url']);
        
        foreach ($metadata['sizes'] as $name => $resolution) {
            $data['resolutions'][$resolution['width']] = array(
                'width' => $resolution['width'],
                'height' => $resolution['height'],
                'type' => $resolution['type'],
                'url' => $baseurl . '/' . $resolution['file'],
                'size' => filesize($basedir . '/' . $resolution['file'])
            );
        }
    }, ['title', 'description']);
}

function get_image($id)
{
    return (object)array(
        'type' => 'image',
        'data' => get_image_object($id)
    );
}

function get_thumbnail($id)
{
    if (!has_post_thumbnail($id)) {
        return false;
    }

    $id = get_post_thumbnail_id($id);
    
    return get_image_object($id);
}

function get_video($id)
{
    return (object)array(
        'type' => 'video',
        'data' => array(
            'video' => get_attachment($id, function (&$data, $metadata, $dir) {
                $data['width'] = $metadata['width'];
                $data['height'] = $metadata['height'];
                $data['size'] = $metadata['filesize'];
            }, ['caption']),
            'thumbnail' => get_thumbnail($id)
       )
    );
}

function get_audio($id)
{
    return (object)array(
        'type' => 'audio',
        'data' => array(
            'audio' => get_attachment($id, function (&$data, $metadata, $dir) {
                $data['bitrate'] = $metadata['bitrate'];
                $data['size'] = $metadata['filesize'];
            }, ['caption']),
            'thumbnail' => get_thumbnail($id)
       )
    );
}

function get_clink($value, $callback = false)
{
    $data = array(
        'value' => $value,
        'type' => '',
        'title' => '',
        'description' => '',
        'img' => ''
    );

    if (is_callable($callback)) {
        $callback($data);
    }
    
    return (object)array(
        'type' => 'link',
        'data' => $data
    );
}

function get_file($id)
{
    return (object)array(
        'type' => 'file',
        'data' =>  get_attachment($id, function (&$data, $metadata, $dir) {
            $info = pathinfo($dir);
            
            $data['size'] = filesize($dir);
            $data['name'] = $info['filename'];
            $data['extension'] = $info['extension'];
        }, ['author', 'title', 'caption', 'description'])
    );
}

function get_gallery($images)
{
    $data = array_map('get_image', $images);

    return (object)array(
        'type' => 'gallery',
        'data' => array(
            'images' => $data
        )
    );
}
