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

function get_ids_array($match)
{
    return array_map('intval', explode(',', $match));
}

function get_id_from_url($url)
{
    return attachment_url_to_postid($url) ? : url_to_postid($url);
}

function get_element($element)
{
    $parse_tag = '/<([\w-]+) data="(.+)" (.*)\/>/';

    preg_match($parse_tag, $element, $matches);

    switch ($matches[1]) {
        case 'image':
            return get_image(intval($matches[2]));
        case 'video':
            return get_video(get_id_from_url($matches[2]));
        case 'audio':
            return get_audio(get_id_from_url($matches[2]));
        case 'anchor':
            $id = attachment_url_to_postid($matches[2]);
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
            return get_gallery(get_ids_array($matches[2]));
        case 'audio_playlist':
            return array('audio_playlist' => get_ids_array($matches[2]));
        case 'video_playlist':
            return array('video_playlist' => get_ids_array($matches[2]));
        case 'youtube':
            return get_clink($matches[2], function (&$data) {
                $data['type'] = 'youtube';
            });
        default:
            if (!empty($element)) {
                return get_text($element);
            }
    }

    return array();
}

function showDOMNode(DOMNode $domNode)
{
    foreach ($domNode->childNodes as $node) {
        print $node->nodeName."\n\n".$node->nodeValue."\n----\n";
        if ($node->hasChildNodes()) {
            showDOMNode($node);
        }
    }
}

function get_elements($post)
{
    // $doc = new DOMDocument();
    // $doc->loadHTML($post->post_content);
    // showDOMNode($doc);

    $content = normalize_wordpress_tags($post->post_content);
    $body = preg_split('/!\[\[\{(.*)\}\]\]/', $content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    $elements = array_map('get_element', $body);

    return array_values(array_filter($elements, function ($val) {
        return !empty($val);
    }));
}

function get_content($id)
{
    $post = get_post($id);
    $lastmodified = $post->post_modified_gmt;

    $format = get_post_format($id);

    return array(
        'author' => get_author($post->author),
        'url' => get_permalink($id),
        'title' => $post->post_title,
        'description' => $post->post_excerpt,
        'tokens' => tokenize($post->post_content),
        'content' => $post->post_content,
        'content_feed' => get_the_content_feed($id),
        'body' => get_elements($post),
        'pubDate' => intval(get_the_date('U', $id)),
        'img' => get_thumbnail($id),
    );
}
