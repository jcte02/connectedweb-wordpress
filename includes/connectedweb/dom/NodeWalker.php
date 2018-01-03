<?php

class NodeWalker
{
    private $node;

    public function __construct($node)
    {
        if ($node instanceof DOMNode) {
            $this->node = $node;
        } else {
            $this->node = new DOMNode();
        }
    }

    public function current()
    {
        return $this->node;
    }

    public function next()
    {
        return $this->node->nextSibling;
    }

    public function parent()
    {
        return $this->node->parentNode;
    }

    public function child()
    {
        return $this->node->firstChild;
    }

    public function right()
    {
        return $this->next() ? $this->node = $this->next() : null;
    }

    public function up()
    {
        return $this->parent() ? $this->node = $this->parent() : null;
    }

    public function down()
    {
        return $this->child() ? $this->node = $this->child() : null;
    }

    public function isText()
    {
        return $this->node->nodeType == XML_TEXT_NODE;
    }

    public function isElement()
    {
        return $this->node->nodeType == XML_ELEMENT_NODE;
    }

    public function tagName()
    {
        return $this->isElement() ? $this->node->tagName : "#text";
    }

    public function wholeText()
    {
        return $this->isText() ? $this->node->wholeText : "";
    }

    /**
     * Return value of the attribute.
     *
     * Return value of the attribute, or an empty string if no attribute with the given name is found.
     *
     * @param string $attibute
     * @return string
     */
    public function attribute($attibute)
    {
        return $this->node->getAttribute($attibute);
    }

    public function interpretStype()
    {
        $attributes = [];
        foreach ($this->node->attributes as $attr) {
            $attributes[$attr->name] = $attr->value;
        }

        $style = $this->attribute("style");
        if ($style) {
            $style_attr = explode(";", $style);
            foreach ($style_attr as $sa) {
                if (!empty($sa)) {
                    list($name, $value) = explode(":", $sa);
                    $attributes[$name] = $value;
                }
            }
        }

        ksort($attributes);
        reset($attributes);

        return $attributes;
    }

    public function attributeSearch($attribute, $callback = null)
    {
        $attributes = $this->interpretStype();
        $result = null;

        if (array_key_exists($attribute, $attributes)) {
            $result = $attributes[$attribute];
        } else {
            $keys = array_keys($attributes);

            foreach ($keys as $key) {
                if (strpos($key, $attribute) !== false) {
                    $result = $attributes[$key];
                    break;
                }
            }
        }

        if ($result && is_callable($callback)) {
            $result = $callback($result);
        }

        return $result;
    }

    public function nextOfType($type)
    {
        while ($this->right()) {
            if ($this->node->nodeType == $type) {
                break;
            }
        }

        return ($this->node->nodeType == $type) ? $this->node : null;
    }

    public function nextText()
    {
        return $this->nextOfType(XML_TEXT_NODE);
    }

    public function firstText()
    {
        $text = $this->nextText();

        return $text ? $text->wholeText() : null;
    }

    public function nextElement($tagName = null)
    {
        if (!$tagName) {
            $this->nextOfType(XML_ELEMENT_NODE);
        } else {
            while ($this->nextOfType(XML_ELEMENT_NODE)) {
                if ($this->tagName() === $tagName) {
                    break;
                }
            }
        }

        return $this->node;
    }
}
