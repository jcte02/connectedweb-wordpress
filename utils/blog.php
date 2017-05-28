<?php

require_once('attachment.php');

function get_blog_logo()
{
    if (!has_custom_logo()) {
        return false;
    }

    $id = get_theme_mod('custom_logo');
    $logo = get_post($id);

    return process_image($logo);
}

function get_blog_header()
{
    if (!has_header_image()) {
        return false;
    }

    $id = get_custom_header()->attachment_id;
    $header = get_post($id);

    return process_image($header);
}

function get_blog_meta()
{
    return array(
        'name' => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'keywords' => get_option('cweb_keywords'),
        'url' => get_bloginfo('url'),
        'language' => get_bloginfo('language'),
        'source' => get_feed_link('cweb') . '?source',
        'logo' => get_blog_logo(),
        'header' => get_blog_header(),
        'cache' => array(
            'cacheable' => get_option('cweb_cacheable'),
            'expiresAfter' => get_option('cweb_expiresAfter')
        )
    );
}
