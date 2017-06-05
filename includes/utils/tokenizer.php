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

// function tokenize($html)
// {
//     $pattern = "/[<\[]([\w]+)([^>\]]*?)(([\s]*\/[>\]])|".
//     "([>\]]((([^<\]]*?|[\[<]\!\-\-.*?\-\-[>\]])|(?R))*)[<\[]\/\\1[\s]*[>\]]))/sm";
//     preg_match_all($pattern, $html, $matches, PREG_OFFSET_CAPTURE);

//     $elements = array();
    
//     foreach ($matches[0] as $key => $match) {
//         $elements[] = (object)array(
//             'node' => $match[0],
//             'offset' => $match[1],
//             'tagname' => $matches[1][$key][0],
//             'attributes' => isset($matches[2][$key][0]) ? $matches[2][$key][0] : '',
//             'omittag' => ($matches[4][$key][1] > -1), // boolean
//             'inner_html' => isset($matches[6][$key][0]) ? $matches[6][$key][0] : ''
//         );
//     }

//     return $elements;
// }

function tag_type($tag_info)
{
    if (!empty($tag_info['close'])) {
        return 'close';
    } elseif (empty($tag_info['plaintext'])) {
        if (substr($tag_info['attributes'], -1) == '/') {
            return 'inline';
        } else {
            return 'open';
        }
    }
    return '';
}

function tokenize_attributes($str)
{
    // \s?(\w+)\=\"([^\"]+)\"
    // $1 -> key
    // $2 -> values

    $reAttribute = '/\s?(?P<key>\w+)\=\"(?P<values>[^\"]+)\"/';
    preg_match_all($reAttribute, $str, $matches, PREG_SET_ORDER);

    $attibutes = array();
                
    foreach ($matches as $match) {
        $attributes[$match['key']] = explode(' ', $match['values']);
    }

    return $attributes;
}

function tokenize($content)
{
    // [<\[](\/)?([\w\d]+)([^>\]]*)[>\]]|([^<\[]+)
    // $1 -> tag: 'close'
    // $2 -> tagname
    // $3 -> attributes
    // $4 -> plaintext
    
    $reToken = '/[<\[](?P<close>\/)?(?P<tagname>[\w\d]+)(?<attributes>[^>\]]*)[>\]]|(?<plaintext>[^<\[]+)/';
    preg_match_all($reToken, $content, $matches, PREG_SET_ORDER);

    $tokens = array();

    foreach ($matches as $match) {
        $tokens[] = array(
            'node' => $match[0],
            'tag' => tag_type($match),
            'tagname' => $match['tagname'],
            'attributes' => tokenize_attributes($match['attributes']),
            'plaintext' => $match['plaintext']
        );
    }

    return $tokens;
}

// global $tags;

// function are_tags()
// {
//     return count($tags);
// }

// function push_tag($tag)
// {
//     $tags[] = $tag;
// }

// function peek_tag()
// {
//     return end($tags);
// }

// function pop_tag()
// {
//     $return = array_pop($tags);
//     if (!are_tags()) {
//         pop_root();
//     }
//     return $return;
// }

// function is_tag($tag, $index, $tokens)
// {
//     return in_array('/' . $tag, array_slice($tokens, $index + 1));
// }

// function is_closing_tag($tag)
// {
//     return $tag == ('/' . peek_tag());
// }

// function nested()
// {
//     return count($tags);
// }

// global $tree;

// global $root;
// global $childrens;

// function push_tree($e)
// {
//     if (nested()) {
//         push_children($e);
//     } else {
//         $tree[] = $e;
//     }
// }

// function last_children()
// {
//     return end($top);
// }

// function push_children($e)
// {
//     if ($root === null) {
//         $root = $e;
//         $root['childrens'] = array();
//         $childrens = $root['childrens'];
//     } else {
//         $childrens[] = $e;
//     }
// }

// function push_root()
// {
//     last_children()['childrens'] = array();
//     $childrens = last_children();
// }

// function pop_root()
// {
//     $tree[] = $root;
//     $root = $childrens = null;
// }

// function parse_tokens($tokens)
// {
//     $tree = $tags = array();
//     $root = $childrens = null;

//     for ($i=0; $i < count($tokens);) {
//         $tok = $tokens[$i];
//         $tag = explode(' ', $tok)[0];

//         if (is_tag($tag, $i, $tokens)) {
//             push_tag($tag);

//             push_tree($tok);

//             if (end($tok) == '/') {
//                 pop_tag();
//             }
//         } elseif (is_closing_tag($tag)) {
//             pop_tag();
//         } else {
//             push_tree(array(
//                 'type' => 'text',
//                 'value' => $tok
//             ));
//         }
//     }

//     // foreach ($tokens as $index => $tok) {
//     //     $tag = explode(' ', $tok)[0];
//     //     switch ($tag) {
//     //         case 'h1':
//     //         case 'h2':
//     //         case 'h3':

//     //             # code...
//     //             break;
            
//     //         case 'blockquote':
//     //         case 'quote':
//     //             break;

//     //         case 'code':
//     //             break;

//     //         default:
//     //             if () {
//     //                 // is tag
//     //             } else {
//     //                 // is text
//     //             }
//     //             break;
//     //     }
//     // }
// }
