<?php

require_once('attachment.php');


function get_audios($post)
{
    $audio = get_attached_media('audio', $post);

    return array_map('process_audio', $audio);
}

function get_videos($post)
{
    $video = get_attached_media('video', $post);

    return array_map('process_video', $video);
}

function get_images($post)
{
    $image = get_attached_media('image', $post);

    return array_map('process_image', $image);
}

function get_files($post)
{
    $file = get_attached_media('file', $post);

    return array_map('process_file', $file);
}

function get_attachments($post)
{
    return array(
        'audio' => get_audios($post),
        'video' => get_videos($post),
        'image' => get_images($post),
        'file' => get_files($post)
    );
}

function get_tag_for($type)
{
    return '![[{<'.$type.' data="$1" $2/>}]]';
}

function normalize_wordpress_tags($content)
{
    $image = '/<img.*wp-image-(\d+).*\/>/'; // $1=id
    $video = '/\[video.*(https?[^"]+).*\](.*)\[\/video\]/'; // $1=url
    $audio = '/\[audio.*(https?[^"]+).*\](.*)\[\/audio\]/'; // $1=url
    $anchor = '/<a.*(https?[^\"]+).*>(.*)<\/a>/'; // $1=url, $2=title
    $gallery = '/\[gallery ids="(.*)"\]/'; // $1=ids
    $audio_playlist = '/\[playlist ids="(.*)"\]/'; // $1=ids
    $video_playlist = '/\[playlist type="video" ids="(.*)"\]/'; // $1=ids
    $youtube_video = '/https?:\/\/www.youtube.com\/watch\?v=([\w\d]+)/'; // $1=id

    $content = preg_replace($image, get_tag_for('image'), $content);
    $content = preg_replace($video, get_tag_for('video'), $content);
    $content = preg_replace($audio, get_tag_for('audio'), $content);
    $content = preg_replace($anchor, get_tag_for('anchor'), $content);
    $content = preg_replace($gallery, get_tag_for('gallery'), $content);
    $content = preg_replace($audio_playlist, get_tag_for('audio_playlist'), $content);
    $content = preg_replace($video_playlist, get_tag_for('video_playlist'), $content);
    $content = preg_replace($youtube_video, get_tag_for('youtube'), $content);

    return $content;
}

function process_element($value)
{
    $parse_tag = '/<([\w-]+) data="(.+)" (.*)\/>/';

    preg_match($parse_tag, $value, $matches);

    switch ($matches[1]) {
        case 'image':
            return array('image' => intval($matches[2]));
        case 'video':
            return array('video' => attachment_url_to_postid($matches[2]));
        case 'audio':
            return array('audio' => attachment_url_to_postid($matches[2]));
        case 'anchor':
            $id = attachment_url_to_postid($matches[2]);
            if ($id != 0 && get_post($id)->post_type == 'attachment') {
                return array('file' => $id);
            } else {
                return array('link' => array('value' => $matches[2], 'title' => $matches[3]));
            }
        case 'gallery':
            return array('gallery' => array_map('intval', explode(',', $matches[2])));
        case 'audio_playlist':
            return array('audio_playlist' => array_map('intval', explode(',', $matches[2])));
        case 'video_playlist':
            return array('video_playlist' => array_map('intval', explode(',', $matches[2])));
        case 'youtube':
            return array('link' => array('value' => $matches[2], 'type' => 'youtube'));
        default:
            return array('text' => $value);
    }
}

function process_content($post)
{
    $content = normalize_wordpress_tags($post->post_content);
    
    $body = preg_split('/!\[\[\{(.*)\}\]\]/', $content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

    return array_map('process_element', $body);
}

function process_post($post)
{
    return array(
        'author' => get_author($post->post_author),
        'url' => get_permalink($post),
        'title' => $post->post_title,
        'description' => $post->post_excerpt,
        'content' => $post->post_content,
        'body' => process_content($post),
        'pubTime' => get_the_date('U', $post),
        'lastwrite' => $post->post_modified_gmt,
        'format' => get_post_format($post),
        'thumbnail' => get_thumbnail($post),
        'attachments' => get_attachments($post)
    );
}
