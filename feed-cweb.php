<?php

// DEV
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('content-type: text/html; charset=utf-8;');

// PRODUCTION
// header('Cache-Control: public, must-revalidate');
// header('Last-Modified: ' . get_lastpostmodified('gmt') . ' GMT');

require_once('cweb-proto.php');

require_once('utils/blog.php');
require_once('utils/post.php');

// '?source' appended to the end of the url
if (array_key_exists('source', $_GET)) {
    $json = encode(render_source());
} else {
    $json = render_feed();
}

 function filter($obj)
 {
     return array_filter((array) $obj, function ($val) {
         return !empty($val);
     });
 }

function encode($obj)
{
    return json_encode(filter($obj), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

function change_key($array, $old_key, $new_key)
{
    if (!array_key_exists($old_key, $array)) {
        return $array;
    }

    $keys = array_keys($array);
    $keys[array_search($old_key, $keys)] = $new_key;

    return array_combine($keys, $array);
}


function render_source()
{
    $source = new Source(null, null);
    $meta = get_blog_meta();

    $source->name = $meta['name'];
    $source->description = $meta['description'];
    $source->keywords = $meta['keywords'];
    $source->url = $meta['url'];
    $source->language = $meta['language'];
    $source->img = $meta['logo'];
    $source->cover = $meta['header'];

    return array(
        'type' => 'source',
        'cwversion' => 1.1,
        'name' => $meta['name'],
        'description' => $meta['description'],
        'url' => $meta['url'],
        'language' => $meta['language']
    );

    return $source->serialize();
}

function render_feed()
{
    while (have_posts()) {
        the_post();
        var_dump(process_post(get_post()));
    }
}

function is_valid_callback($subject)
{
    $identifier_syntax
      = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';

    $reserved_words = array('break', 'do', 'instanceof', 'typeof', 'case',
      'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue',
      'for', 'switch', 'while', 'debugger', 'function', 'this', 'with',
      'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum',
      'extends', 'super', 'const', 'export', 'import', 'implements', 'let',
      'private', 'public', 'yield', 'interface', 'package', 'protected',
      'static', 'null', 'true', 'false');

    return preg_match($identifier_syntax, $subject)
        && ! in_array(mb_strtolower($subject, 'UTF-8'), $reserved_words);
}

// JSON if no callback
if (!isset($_GET['callback'])) {
    // header('Content-Type: application/connectedweb+json; charset=utf-8');
    exit($json);
}

// JSONP if valid callback
if (is_valid_callback($_GET['callback'])) {
    header('Content-Type: application/connectedweb+jsonp; charset=utf-8');
    exit("{$_GET['callback']}($json)");
}

// Otherwise, bad request
header('status: 400 Bad Request', true, 400);
