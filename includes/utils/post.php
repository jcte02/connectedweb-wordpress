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

require_once('attachment.php');
require_once('tokenizer.php');


function get_ids($match)
{
    return array_map('intval', explode(',', $match));
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
    if (count($token['attributes']) > 1) {
        $return = array_map(array_map(function ($key, $values) {
            return $key . '="' . implode(' ', $values) . '"';
        }, array_keys($token['attributes']), $token['attributes']));
        
        if (count($return) > 1) {
            array_unshift($return, ' ');
            $return[] = ' ';
        }
        
        return implode(' ', $return);
    }

    return '';
}

function rebuild_tags($token)
{
    switch ($token['tagname']) {
        case 'b':
        case 'strong':
        case 'br':
        case 'i':
        case 'a':
        case 'em':
        case 'ul':
        case 'ol':
        case 'li':
        case 'strike':
        case 'del':
            $valid = true;
            break;
        default:
            $valid = false;
            break;
    }

    if (empty($token['tag'])) {
        return $token['plaintext'];
    } elseif ($token['tag'] === 'inline') {
        if ($valid) {
            return '<' . $token['tagname'] . rebuild_attributes($token) . '/>';
        }
    } else {
        $return = array();
        if ($valid) {
            $return[] = '<' . $token['tagname'] . rebuild_attributes($token) . '>';
        }

        foreach ($token['childrens'] as $child) {
            $return[] = rebuild_tags($child);
        }

        if ($valid) {
            $return[] = '</' . $token['tagname'] . '>';
        }
        return flatten($return);
    }
}

function rebuild_childs($arr)
{
    $return = array();

    foreach ($arr as $child) {
        $return[] = rebuild_tags($child);
    }

    return flatten($return);
}

function get_text_safe($value, $callback=false)
{
    $text = trim($value);

    if (!empty($text)) {
        return get_text($text, $callback);
    }
}

function get_element($token)
{
    if (empty($token['plaintext'])) {
        switch ($token['tagname']) {
            case 'h1':
            case 'h2':
            case 'h3':
                return get_text_safe(implode(rebuild_childs($token['childrens'])), function (&$data) use ($token) {
                    $data['appearance'] = $token['tagname'];
                });
            case 'blockquote':
                return get_text_safe(implode(rebuild_childs($token['childrens'])), function (&$data) {
                    $data['appearance'] = 'quote';
                });
            case 'pre':
            case 'code':
                return get_text_safe(implode(rebuild_childs($token['childrens'])), function (&$data) {
                    $data['appearance'] = 'code';
                });
            case 'p':
                return get_text_safe(implode(rebuild_childs($token['childrens'])));
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
                $url = $token['childrens'][0]['plaintext'];
                preg_match('/^https?:\/\/w*\.?(?P<domain>[\w\d]+)\./', $url, $match);
                return get_clink($url, function (&$data) use ($match) {
                    $data['type'] = $match['domain'];
                });
                        
            default:
                return get_text_safe(implode(rebuild_tags($token)));
        }
    } else {
        return get_text_safe($token['plaintext']);
    }
}

function get_body($ast)
{
    $return = array();

    foreach ($ast as $token) {
        $element = get_element($token);

        if (!is_null($element)) {
            if (is_array($element)) {
                array_merge($return, $element);
            } else {
                if ($element->type == 'text') {
                    if ($element->data['appearance'] == '') {
                        while ($pos = strpos($element->data['value'], "\n")) {
                            $text = $element->data['value'];
                        
                            $str1 = substr($text, 0, $pos);
                            $str2 = substr($text, $pos);

                            $return[] = get_text_safe($str1);

                            $element = get_text_safe($str2);
                            $split = true;
                        }
                    }
                    if (end($return)->type == 'text' && $element->data['appearance'] == end($return)->data['appearance'] && !$split) {
                        end($return)->data['value'] .= $element->data['value'];
                    } else {
                        $return[] = $element;
                    }
                } else {
                    $return[] = $element;
                }
            }
        }
    }

    return $return;
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
