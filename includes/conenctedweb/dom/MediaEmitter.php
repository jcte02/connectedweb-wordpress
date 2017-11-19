<?php

require_once '../connectedweb.php';

class MediaEmitter
{
    public static function parse($node)
    {
        switch ($node->tagName()) {
            case 'a':
                if ($node->child()->nodeName == 'img') {
                    $node->down();
                    return new Image([
                        'url' => $node->attribute('src'),
                        'width' => $node->attributeSearch('width', 'intval'),
                        'height' => $node->attributeSearch('height', 'intval')
                    ]);
                } else {
                    // if href ends in .file !ht*
                    return new Link([
                        'value' => $node->attribute('href'),
                        'title' => $node->attribute('title'),
                        'description' => $node->nextText()->wholeText()
                    ]);
                }
                break;
            case 'audio':
                break;
            case 'video':
                break;
            case 'img':
                return new Image([
                    'url' => $node->attribute('src'),
                    'width' => $node->attributeSearch('width', 'intval'),
                    'height' => $node->attributeSearch('height', 'intval')
                ]);
                break;
            case 'iframe':
                break;
        }
    }
}
