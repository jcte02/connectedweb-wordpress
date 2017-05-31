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

include('includes/output/header.php');

require_once('includes/utils/blog.php');
require_once('includes/utils/post.php');

$feed = get_blog_feed(function (&$feed) {
    while (have_posts()) {
        the_post();
        $feed['contents'][] = get_content(get_the_ID());
    }
});

$json = encode($feed);

include('includes/output/json_output.php');
