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

require_once('attachment.php');
require_once('tokenizer.php');


function get_ids($match)
{
    return array_map('intval', explode(',', $match));
}

function id_from_url($url)
{
    return attachment_url_to_postid($url) ? : url_to_postid($url);
}

function wp_image($img)
{
    $id = 0;

    foreach ($img['attributes']['class'] as $value) {
        if (preg_match('/wp-image-(?<id>\d+)/', $value, $match)) {
            $id = intval($match['id']);
        }
    }

    return $id;
}

function flatten(array $array)
{
    $return = array();
    array_walk_recursive($array, function ($a) use (&$return) {
        $return[] = $a;
    });
    return $return;
}

function rebuild_attributes($token)
{
    $return = array_map(array_map(function ($key, $values) {
        return $key . '="' . implode(' ', $values) . '"';
    }, array_keys($token['attributes']), $token['attributes']));

    return implode(' ', $return);
}

function rebuild_tags($token)
{
    if (empty($token['tag'])) {
        return $token['plaintext'];
    } elseif ($token['tag'] === 'inline') {
        return '<' . $token['tagname'] . ' ' . rebuild_attributes($token) . ' />';
    } else {
        $return = array();
        $return[] = '<' . $token['tagname'] . ' ' . rebuild_attributes($token) . '>';

        foreach ($token['childrens'] as $child) {
            $return[] = rebuild_tags($child);
        }

        $return[] = '</' . $token['tagname'] . '>';
        return flatten($return);
    }
}

function get_element($token)
{
    if (empty($token['plaintext'])) {
        switch ($token['tagname']) {
            case 'h1':
            case 'h2':
            case 'h3':
                $text = get_text($token['childrens'][0]['plaintext'], function (&$data) use ($token) {
                    $data['appearance'] = $token['tagname'];
                });
                return $text;
            case 'blockquote':
                $text = get_text($token['childrens'][0]['plaintext'], function (&$data) {
                    $data['appearance'] = 'quote';
                });
                return $text;
            case 'code':
                $text = get_text($token['childrens'][0]['plaintext'], function (&$data) {
                    $data['appearance'] = 'code';
                });
                return $text;

            case 'img':
                if (wp_image($token)) {
                    return get_image(wp_image($token));
                } else {
                    return (object)array(
                        'type' => 'image',
                        'data' => array(
                            'url' => $token['attributes']['src'][0],
                            'width' => intval($token['attributes']['width'][0]),
                            'height' => intval($token['attributes']['height'][0])
                        )
                    );
                }
            
            case 'caption':
                $image = get_element($token['childrens'][0]);
                $caption = $token['childrens'][1]['plaintext'];
                
                $image->data['caption'] = $caption;
                return $image;

            case 'video':
                return get_video(id_from_url($token['attributes'][0][0]));
            case 'audio':
                return get_video(id_from_url($token['attributes'][0][0]));

            case 'a':
                $id = id_from_url($token['attributes']['href'][0]);
                
                if ($id != 0 && get_post($id)->post_type == 'attachment') {
                    $type = get_attachment($id)['type'];
                    preg_match('/(.+)\//', $type, $mime);
                    switch ($mime[1]) {
                        case 'audio':
                            return get_audio($id);
                        case 'video':
                            return get_video($id);
                        case 'image':
                            return get_image($id);
                        case 'application':
                            return get_file($id);
                    }
                    return get_file($id);
                } else {
                    return get_clink($matches[2], function (&$data) {
                        $data['title'] = $matches[3];
                    });
                }

            case 'gallery':
                return get_gallery(get_ids($token['attributes']['ids'][0]));
            
            case 'playlist':
                $type = is_null($token['attributes']['type']) ? 'audio' : 'video';
                return array_map('get_' . $type, get_ids($token['attributes']['ids'][0]));
            
            case 'embed':
                return get_clink($token['childrens'][0]['plaintext'], function (&$data) {
                    $data['type'] = 'youtube';
                });
            
            default:
                return get_text(implode(rebuild_tags($token)));
        }
    } else {
        return get_text($token['plaintext']);
    }
}

function get_body($ast)
{
    return flatten(array_map('get_element', $ast));
}

function get_content($id)
{
    $post = get_post($id);
    $lastmodified = $post->post_modified_gmt;

    // $format = get_post_format($id);
    
    return array(
        'author' => get_author($post->author),
        'url' => get_permalink($id),
        'title' => $post->post_title,
        'description' => $post->post_excerpt,
        'tokens' => tokenize($post->post_content),
        'ast' => ast(tokenize($post->post_content)),
        'content' => $post->post_content,
        'body' => get_body(ast(tokenize($post->post_content))),
        'pubDate' => intval(get_the_date('U', $id)),
        'img' => get_thumbnail($id),
    );
}
