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

if (!isset($_GET['callback'])) {
    header('Content-Type: application/connectedweb+json; charset=utf-8');
    exit($json);
}

if (is_valid_callback($_GET['callback'])) {
    header('Content-Type: application/connectedweb+jsonp; charset=utf-8');
    exit("{$_GET['callback']}($json)");
}

header('status: 400 Bad Request', true, 400);
