<?php

function get_author($user_id = false)
{
    return array(
        'name' => get_the_author_meta('display_name', $user_id),
        'type' => get_the_author_meta('user_type', $user_id),
        'url' => get_the_author_meta('url', $user_id),
        'age' => get_the_author_meta('user_age', $user_id),
        'gender' => get_the_author_meta('user_gender', $user_id)
    );
}

function get_thumbnail($post)
{
    if (!has_post_thumbnail($post->ID)) {
        return false;
    }

    $id = get_post_thumbnail_id($post->ID);
    $thumb = get_post($id);

    return process_image($thumb);
}

function process_attachment($attachment)
{
    return array(
        'id' => $attachment->ID,
        'author' => get_author($attachment->post_author),
        'url' => wp_get_attachment_url($attachment->ID),
        'title' => $attachment->post_title,
        'caption' => $attachment->post_excerpt,
        'description' => $attachment->post_content,
        'lastmodified' => $attachment->post_modified_gmt,
        'mime-type' => $attachment->post_mime_type,
        'thumbnail' => get_thumbnail($attachment)
    );
}

function process_file($attachment)
{
    $data = process_attachment($attachment);
    
    $dir = get_attached_file($data['id']);
    $info = pathinfo($dir, PATHINFO_FILENAME | PATHINFO_EXTENSION);

    $data['size'] = filesize($dir);
    $data['name'] = $info['filename'];
    $data['extension'] = $info['extension'];

    return $data;
}

function process_resolution($resolution, $basedir, $baseurl)
{
    return array(
        'width' => $resolution['width'],
        'height' => $resolution['height'],
        'mime-type' => $resolution['mime-type'],
        'url' => $baseurl . '/' . $resolution['file'],
        'size' => filesize($basedir . '/' . $resolution['file'])
    );
}

function process_image($attachment)
{
    $data = process_attachment($attachment);
    $metadata = wp_get_attachment_metadata($data['id']);

    $dir = get_attached_file($data['id']);

    $data['size'] = filesize($dir);
    $data['width'] = $metadata['width'];
    $data['height'] = $metadata['height'];
    
    $data['resolutions'] = array();

    $basedir = dirname($dir);
    $baseurl = dirname($data['url']);

    foreach ($metadata['sizes'] as $name => $resolution) {
        $data['resolutions'][$resolution['width']] = process_resolution($resolution, $basedir, $baseurl);
    }
    
    return $data;
}

function process_video($attachment)
{
    $data = process_attachment($attachment);
    $metadata = wp_get_attachment_metadata($data['id']);

    $data['width'] = $metadata['width'];
    $data['height'] = $metadata['height'];
    $data['size'] = $metadata['filesize'];

    return $data;
}

function process_audio($attachment)
{
    $data = process_attachment($attachment);
    $metadata = wp_get_attachment_metadata($data['id']);

    $data['bitrate'] = $metadata['bitrate'];
    $data['size'] = $metadata['filesize'];

    return $data;
}
