<?php

include_once "connectedweb.php";
require_once 'dom/NodeWalker.php';
require_once 'dom/TextEmitter.php';
require_once 'dom/MediaEmitter.php';

class DOMWalker
{
    private $node;

    private $body;
    private $textEmitter;

    private $text = array('p', 'h1', 'h2', 'h3', 'pre', 'code', 'blockquote',);
    private $media = array('audio', 'video', 'img', 'a', 'iframe');

    private $whitelist = array('br', 'b', 'strong', 'i', 'a', 'em', 'strike', 'del', 'ol', 'ul', 'li');
    private $blacklist = array('script');

    public function __construct()
    {
        $this->body = array();
        $this->textEmitter = new TextEmitter($this);
    }

    public function insert($block)
    {
        if ($block) {
            $this->body[] = $block;
        }
    }

    public function parse($html)
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?>' . $html);

        $body = $dom->getElementsByTagName('body')->item(0);
        $this->node = new NodeWalker($body);

        while ($this->next()) {
            if ($this->isBlock()) {
                if ($this->isTextBlock()) {
                    $this->textEmitter->emit($this->node->tagName());
                } elseif ($this->isMediaBlock()) {
                    if ($this->node->tagName() == 'a' && $this->textEmitter->hasText()) {
                        // handle inline-text link
                        $this->textEmitter->opentag("a href=\"" . $this->node->attribute("href") . "\" target=\"_blank\"");
                    } else {
                        $this->textEmitter->store();
                        $this->insert(MediaEmitter::parse($this->node));
                        $this->node->right();
                        $this->textEmitter->restore();
                    }
                }
            } elseif ($this->isWhitelisted()) {
                $this->textEmitter->opentag($this->node->tagName());
            } elseif ($this->node->isText()) {
                $this->textEmitter->pushtext($this->node->wholeText());
            }
        }
        $this->textEmitter->flush();
        return $this->body;
    }

    private function next()
    {
        if ($this->node->child() && !$this->isBlacklisted()) {
            return $this->node->down();
        } else {
            if ($this->node->next()) {
                return $this->node->right();
            } else {
                while ($this->node->up()) {
                    if ($this->isWhitelisted() && $this->textEmitter->hasText()) {
                        $this->textEmitter->closetag($this->node->tagName());
                    } elseif ($this->isTextBlock()) {
                        $this->textEmitter->closeblock($this->node->tagName());
                    }

                    if ($this->node->next()) {
                        return $this->node->right();
                    }
                }
                return null;
            }
        }
    }

    public function isMediaBlock()
    {
        return in_array($this->node->tagName(), $this->media);
    }

    public function isTextBlock()
    {
        return in_array($this->node->tagName(), $this->text);
    }

    public function isBlock()
    {
        return $this->isMediaBlock() || $this->isTextBlock();
    }

    public function isWhitelisted()
    {
        return in_array($this->node->tagName(), $this->whitelist);
    }

    public function isBlacklisted()
    {
        return in_array($this->node->tagName(), $this->blacklist);
    }
}
