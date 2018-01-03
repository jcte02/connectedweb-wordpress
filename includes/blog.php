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
require_once('connectedweb/connectedweb.php');


function get_blog_logo()
{
    if (has_custom_logo()) {
        $id = get_theme_mod('custom_logo');
        return new ImageObject(get_image_object($id));
    }

    return null;
}

function get_blog_header()
{
    $url = get_header_image();

    if ($url) {
        if ($id = id_from_url($url)) {
            $params = get_image_object($id);
        } else {
            $header = get_custom_header();
            $params = array(
                'url' => $header->url,
                'width' => $header->width,
                'height' => $header->height,
                'caption' => $header->description
            );
        }

        return new ImageObject($params);
    }

    return null;
}

function get_most_popular_tags($n)
{
    $tags = get_terms('post_tag', array(
        'number' => $n,
        'orderby' => 'count',
        'order' => 'DESC'
    ));

    $return = array_map(function ($tag) {
        return $tag->name;
    }, $tags);

    return implode(', ', $return);
}

function get_keywords()
{
    return get_option('use_popular_tags', 1) ? get_most_popular_tags(5) : get_option('custom_keywords');
}

function get_cache_settings()
{
    if (get_option('use_default_cache', 1)) {
        $cacheable = 1;
        $expiresAfter = 3600000;
    } else {
        $cacheable = get_option('can_cache');
        $expiresAfter = get_option('cache_expire_milliseconds');
    }

    return new Cache([
        'cacheable' => $cacheable,
        'expiresAfter' => $expiresAfter
    ]);
}

function get_blog_meta($not = array())
{
    $data = array(
        'name' => html_entity_decode(get_bloginfo('name')),
        'description' => html_entity_decode(get_bloginfo('description')),
        'keywords' => html_entity_decode(get_keywords()),
        'url' => get_bloginfo('url'),
        'source' => get_feed_link('connectedweb/source'),
        'language' => get_bloginfo('language'),
        'img' => get_blog_logo(),
        'cover' => get_blog_header(),
        'cache' => get_cache_settings()
    );

    foreach ($not as $key) {
        unset($data[$key]);
    }

    return $data;
}

function get_blog_source()
{
    return get_blog_meta(['source', 'cache']);
}

function get_blog_feed()
{
    return get_blog_meta(['url', 'img', 'cover']);
}
