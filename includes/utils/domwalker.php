<?php
// TODO copyright header

// TODO cweb classes or cweb_#{element} functions
// TODO doc_class, doc_f documentation

// TODO replace function in attachment.php
function get_attachment($id, $opt = array())
{
    $r =  array(
    'type' => 'robeh',
    'data' => 'coseh');

    // or is it better to overwrite $opt with $r?
    return array_merge($r, array('data' => $opt));
}

function href($node)
{
    return @$node->attributes->getNamedItem('href')->textContent;
}

function is_gallery_item($node)
{
    // an 'a' node which first children is an 'img' node
    return (($node->nodeName == 'a') &&
            $node->hasChildNodes()  && ($node->firstChild->nodeName == 'img'));
}

// $cur must point to last node handled by this function
// this function must push directly to $body
function parse_media(&$cur, &$body)
{
    $cur = new DOMNode();
    switch ($cur->nodeName) {
        case 'a':
            if (is_gallery_item($cur)) {
                // this is an image gallery

                // ids of the gallery images
                $gallery = array();

                // is this the last gallery item?
                // past_gallery, in_gallery
                $last_gallery_item = false;

                // until we parse the last node of the gallery
                while (!$last_gallery_item) {
                    // convert url href to image id and store in array
                    $gallery[] = id_from_url(href($cur));
                    // check if next sibling if a gallery item
                    if (is_gallery_item($cur->nextSibling)) {
                        // next sibling is gallery item, advance to it
                        $cur = $cur->nextSibling;
                    } else {
                        // next sibling is NOT a gallery item
                        // this is the last item of the gallery, exit loop
                        $last_gallery_item = true;
                    }
                }

                // insert cweb gallery into body
                $body[] = get_gallery($gallery);
            } else {
                // this is a normal link

                // first #text child or false
                $title = $cur->

                $link = new Link();
                $link->url = href($cur);
                $link->title = $text;

                // class _get, _set, _call
                // _set does nothing if empty($val)

                $link->as('link');
                // return [type=>$val, data=>$this]

                $body[] = cweb_link(href($cur), array('title' => ''));

                $body[] = new Link([
                    'value' => href($cur)
                ]);
            }

            // create new link
            // value: $node->attributes['href']
            // title: $node->textContent

            break;

        case 'img':
            break;

        case 'audio':
            // audio['id'] -> audio-#{id}-#{n}
            // child -> <source>['type', 'src'] -> src = path + _?=n
            //       -> <a>['href'], <a>.textContent = path
            break;

        case 'div':
            // if class=wp-video
            // child -> <video>['id'] -> video-#{id}-#{n}
            // child -> <source>['type', 'src'] -> src = path + _?=n
            //       -> <a>['href'], <a>.textContent = path
            break;

        case 'iframe':
            // create link with type based on attributes['src']

            // https://www.youtube.com/embed/yD_w5hhQL8M?feature=oembed
            // https://www.youtube.com/embed/videoseries?list=PLYSecr3PvKGLXuo8ZaTCJuTSFQZBKbWkY


            break;
    }
}

function textmode($tagname)
{
    switch ($tagname) {
        case 'p':
            return 'none';
        case 'h1':
        case 'h2':
        case 'h3':
            return $tagname;
        case 'pre':
        case 'code':
            return 'code';
        case 'blockquote':
            return 'quote';
    }
}

// textblock global state
$block;
$blockmode;
$textmode = array();

// flush textblock if any
function flush_textblock()
{
    if (!empty($block)) {
        $body[] = get_text(flatten($block), function (&$data) use ($blockmode) {
            $data['appearance'] = $blockmode;
        });
    }
    $block = $blockmode = null;
}

// allocate new textblock
// flush previous textblock if any
function init_textblock($mode)
{
    // flush previuos textblock if any
    flush_textblock();

    // init new textblock
    $block = array();
    $blockmode = $mode;
}

// push text into textblock
// doesn't do anything is textblock or text are null
function push_text($text)
{
    if (!empty($text) && !empty($block)) {
        $block[] = $text;
    }
}

// save mode of current textblock and flush it
// doesn't do anything if textblock is not allocated
function push_textmode()
{
    if (!empty($block)) {
        array_push($textmode, $blockmode);
        flush_textblock();
    }
}

// create new block with saved mode
// doesn't do anything if mode was not saved
function pop_textmode()
{
    $mode = array_pop($textmode);
    if ($mode != null) {
        init_textblock($mode);
    }
}

/**
 * @return string[] the body elements
 */
function domwalk()
{
    // block types
    $text = array('p', 'h1', 'h2', 'h3', 'pre', 'code', 'blockquote');
    $media = array('audio', 'video', 'img', 'a', 'iframe');

    // start of a new block
    $halt = array_merge($text, $media);

    // text elements
    $whitelist = array('br', 'b', 'strong', 'i', 'em', 'strike', 'del', 'ol', 'ul', 'li');
    $blacklist = array('script');

    // body as array of cweb elements
    $body = array();

    // load rss2 feed content as html
    $doc = new DOMDocument();
    @$doc->loadHTML(get_the_content_feed('rss2'));

    // root is body node, start parsing from its firt child
    $root = $doc->getElementsByTagName('body')->item(0);
    $cur = $root->firstChild;

    // return empty array if body node has no childrens
    if (!$root->hasChildNodes()) {
        return array();
    }

    // until we backtrack to the root node
    while ($cur != $root) {
        if (in_array($cur->nodeName, $halt)) {
            // we hit a new block
            if (in_array($cur->nodeName, $text)) {
                // allocate new textblock
                // previous textblock is flushed if any
                init_textblock(textmode($cur->nodeName));
            } else {
                // this is a media block
                // save mode of current textblock and flush it
                // if textblock isn't active, mode is not saved
                push_textmode();
                // handle media element, $cur and $body passed by reference
                // $cur points to the last node handled by the routine
                parse_media($cur, $body);
                // init new textblock and restore saved mode
                // no textblock is allocated if mode was not saved
                pop_textmode();
                // increment to next node
                goto next;
            }
        } elseif (in_array($cur->nodeName, $blacklist)) {
            // this node is to be ignored along with its children,
            // increment to next node
            goto next;
        } elseif (in_array($cur->nodeName, $whitelist)) {
            push_text('<' . $cur->nodeName . '>');
        } elseif ($cur->nodeName == '#text') {
            push_text($cur->textContent);
        }

        // process child nodes
        // when we are done, we will return to the next node
        if ($cur->hasChildNodes()) {
            $cur = $cur->firstChild;
            continue;
        }

        next:
        // check if there is another node in the current scope to process
        // if not, jump to parent node and repeat
        // break when we backtrack to root node
        while ($cur->nextSibiling == null) {
            $cur = $cur->parentNode;

            if ($cur == $root) {
                break;
            }

            if (in_array($cur->nodeName, $whitelist)) {
                push_text('</' . $cur->nodeName . '>');
            }
        }

        // advance to next node if we are not at the root
        if ($cur != $root) {
            $cur = $cur->nextSibling;
        }
    }

    // flush last textblock if any
    flush_textblock();

    return $body;
}
