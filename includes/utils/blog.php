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

function get_blog_logo()
{
    if (!has_custom_logo()) {
        return false;
    }

    $id = get_theme_mod('custom_logo');
    
    return get_image_object($id);
}

function get_blog_header()
{
    if (!has_header_image()) {
        return false;
    }

    $id = get_custom_header()->attachment_id;

    return get_image_object($id);
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

    return array(
        'cacheable' => $cacheable,
        'expiresAfter' => $expiresAfter
    );
}

function get_blog_meta($callback = false, $not = array())
{
    $data = array(
        'name' => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'keywords' => get_keywords(),
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

    if (is_callable($callback)) {
        $callback($data);
    }
    
    return $data;
}

function get_blog_source()
{
    return get_blog_meta(function (&$data) {
        $data['type'] = 'source';
        $data['cwversion'] = 1.1;
    }, ['source', 'cache']);
}

function get_blog_feed($callback = false)
{
    $data = get_blog_meta(function (&$data) {
        $data['type'] = 'feed';
        $data['cwversion'] = 1.1;
    }, ['url', 'img', 'cover']);

    if (is_callable($callback)) {
        $callback($data);
    }

    return $data;
}
