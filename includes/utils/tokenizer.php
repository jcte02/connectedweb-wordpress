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
    return false;
}

function tokenize_attributes($str)
{
    // \s?(\w+)\=\"([^\"]+)\"
    // $1 -> key
    // $2 -> values

    $reAttribute = '/\s?(?P<key>\w+)\=\"(?P<values>[^\"]+)\"/';
    preg_match_all($reAttribute, $str, $matches, PREG_SET_ORDER);

    $attributes = array();
                
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

    foreach ($matches as $key => $match) {
        $tokens[] = array(
            'id' => $key,
            // 'node' => $match[0],
            'tag' => tag_type($match),
            'tagname' => $match['tagname'],
            'attributes' => tokenize_attributes($match['attributes']),
            'plaintext' => $match['plaintext']
        );
    }

    return $tokens;
}

function ast($tokens)
{
    $tree = array_merge(
        array(
            'root' => array(
                'childrens' => array()
            )
        ),
    $tokens);

    $parent = new SplStack();
    $parent->push('root');

    foreach ($tokens as $key => $token) {
        switch ($token['tag']) {
            case 'open':
                $tree[$key]['parent'] = $parent->top();
                $tree[$parent->top()]['childrens'][] = &$tree[$key];
                $parent->push($key);
                break;
            case 'close':
                $parent->pop();
                break;
            default:
                $tree[$key]['parent'] = $parent->top();
                $tree[$parent->top()]['childrens'][] = $tree[$key];
                break;
        }
    }

    return $tree['root']['childrens'];
}
