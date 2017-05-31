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

    $return = array();

    foreach ($tags as $tag) {
        $return[$tag->name] = $tag->count;
    }

    return array_map(function ($tag) {
        return $tag->name;
    }, $tags);
}

function get_blog_meta($callback = false, $not = array())
{
    $data = array(
        'name' => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'keywords' => get_option('blog_keywords', implode(', ', get_most_popular_tags(5))),
        'url' => get_bloginfo('url'),
        'source' => get_feed_link('connectedweb/source'),
        'language' => get_bloginfo('language'),
        'img' => get_blog_logo(),
        'cover' => get_blog_header(),
        'cache' => array(
            'cacheable' => get_option('can_cache', 1),
            'expiresAfter' => get_option('cache_expire_milliseconds', 3600000)
        )
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
