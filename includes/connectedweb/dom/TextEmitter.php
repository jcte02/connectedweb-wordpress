<?php

class TextEmitter
{
    public function tag2appearance($tagname)
    {
        switch ($tagname) {
            case null:
            case 'p':
                return null;
            case 'h1':
            case 'h2':
            case 'h3':
                return $tagname;
            case 'pre':
            case 'code':
                return 'code';
            case 'q':
            case 'blockquote':
                return 'quote';
        }
    }

    private $tag;
    public $value;
    private $appearance;

    private $dom;
    private $store = array();

    public function __construct($dom)
    {
        $this->dom = $dom;
    }

    public function isAlphaText()
    {
        return isset($this->value) && !empty(trim(html_entity_decode($this->value)));
    }

    public function flush()
    {
        if ($this->isAlphaText()) {
            $this->dom->insert(
                new Text(
                    [
                        'value' => trim(html_entity_decode($this->value)),
                        'appearance' => $this->appearance
                    ]
                )
            );
        }

        unset($this->tag, $this->value, $this->appearance);
    }

    public function emit($tagName)
    {
        $appearance = $this->tag2appearance($tagName);

        if (is_null($appearance) && isset($this->appearance)) {
            // p inside a block
            if ($this->isAlphaText()) {
                // emulate paragraph inside block
                $this->opentag('br');
                $this->opentag('br');
            }
            return;
        } elseif ($tagName != 'p' && $this->tag == $tagName) {
            // p inside p is flushed, block inside block is merged.
            return;
        }

        $this->flush();

        $this->tag = $tagName;
        $this->value = "";
        $this->appearance = $appearance;
    }

    public function closeblock($tagName)
    {
        if (isset($this->tag) && $this->tag === $tagName) {
            $this->flush();
        }
    }

    public function pushtext($text)
    {
        if (!$this->hasText()) {
            $this->emit('p');
        }

        $this->value = $this->value . $text;
    }

    public function opentag($tagName)
    {
        $this->pushtext("<" . $tagName . ">");
    }

    public function closetag($tagName)
    {
        $this->pushtext("</" . $tagName . ">");
    }

    public function store()
    {
        if ($this->hasText()) {
            array_push($this->store, $this->appearance);
            $this->flush();
        }
    }

    public function restore()
    {
        $appearance = array_pop($this->store);
        if ($appearance) {
            $this->emit('p');
            $this->appearance = $appearance;
        }
    }

    public function hasText()
    {
        return isset($this->value);
    }
}
