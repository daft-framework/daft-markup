<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup;

use DOMAttr;
use DOMElement;
use DOMNode;
use DOMText;
use InvalidArgumentException;
use Masterminds\HTML5;

class Markup
{
    const SUPPORTED_ARRAY_ATTRIBUTES = [
        '!element',
        '!attributes',
        '!content',
    ];

    const SELF_CLOSING_ELEMENTS = [
        'area',
        'base',
        'br',
        'col',
        'command',
        'embed',
        'hr',
        'img',
        'input',
        'keygen',
        'link',
        'meta',
        'param',
        'source',
        'track',
        'wbr',
    ];

    const BOOLEAN_ELEMENT_ATTRIBUTES = [
        'contenteditable',
        'draggable',
        'hidden',
        'itemscope',
        'spellcheck',
    ];

    const REGEX_ELEMENT_NAME =
        '/^(?:[a-z]+[a-z0-9]*(?:\-[a-z0-9]+)*(?:\:[a-z]+[a-z0-9]*(?:\-[a-z0-9]+)*){0,1})$/';

    const REGEX_ATTRIBUTE_NAME =
        '/^(?:[a-z]+[a-z0-9]*(?:\-[a-z0-9]+)*(?:\:[a-z]+[a-z0-9]*(?:\-[a-z0-9]+)*){0,1})$/';

    /**
    * @param array<int, scalar|array<int|string, mixed>> $markupContent
    */
    public function MarkupCollectionToMarkupString(
        array $markupContent,
        bool $xml_style = false,
        int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
        string $encoding = 'UTF-8',
        bool $double_encode = false
    ) : string {
        $out = '';

        foreach ($markupContent as $content) {
            if (is_scalar($content)) {
                $out .= htmlentities((string) $content, $flags, $encoding, $double_encode);
            } elseif (is_array($content)) {
                /*
                These args aren't indented like I'd normally indent them due to xdebug coverage
                */
                $out .= $this->MarkupArrayToMarkupString(
                    $content, $xml_style, $flags, $encoding, $double_encode
                );
            }
        }

        return $out;
    }

    /**
    * @param array<int|string, mixed> $markup
    */
    protected function MarkupArrayToMarkupStringValidateMarkup(array $markup) : void
    {
        if ( ! array_key_exists('!element', $markup)) {
            throw new InvalidArgumentException('Element not specified!');
        } elseif ( ! is_string($markup['!element'])) {
            throw new InvalidArgumentException('Element not specified as string!');
        } elseif (
            ! preg_match(self::REGEX_ELEMENT_NAME, $markup['!element'])
        ) {
            throw new InvalidArgumentException(sprintf(
                'Element not valid! (%s)',
                $markup['!element']
            ));
        } elseif (isset($markup['!content'])) {
            if ( ! is_array($markup['!content'])) {
                throw new InvalidArgumentException(
                    'Element content must be specified as an array!'
                );
            }

            /**
            * @var array<int|string, mixed> $markupContent
            */
            $markupContent = $markup['!content'];

            foreach (array_keys($markupContent) as $key) {
                if ( ! is_scalar($markupContent[$key]) && ! is_array($markupContent[$key])) {
                    throw new InvalidArgumentException(
                        'Element content must be scalar or an array!'
                    );
                }
            }
        }

        foreach (array_keys($markup) as $k) {
            if ( ! in_array($k, self::SUPPORTED_ARRAY_ATTRIBUTES, true)) {
                throw new InvalidArgumentException(sprintf('Unsupported array key! (%s)', $k));
            }
        }
    }

    /**
    * @param array<int|string, mixed> $markup
    *
    * @return array<string, scalar|scalar[]>
    */
    protected function MarkupArrayToMarkupStringValidateMarkupAttributes(array $markup) : array
    {
        if (isset($markup['!attributes'])) {
            if ( ! is_array($markup['!attributes'])) {
                throw new InvalidArgumentException('Attributes not specified as an array!');
            }

            /**
            * @var array<int|string, mixed> $attributes
            */
            $attributes = $markup['!attributes'];

            foreach (array_keys($attributes) as $attr) {
                if ( ! is_string($attr)) {
                    throw new InvalidArgumentException('Attribute keys must be strings!');
                } elseif ( ! preg_match(self::REGEX_ATTRIBUTE_NAME, $attr)) {
                    throw new InvalidArgumentException(sprintf(
                        'Attribute name invalid! (%s)',
                        $attr
                    ));
                } elseif (is_array($attributes[$attr])) {
                    /**
                    * @var int $key
                    */
                    foreach (array_keys($attributes[$attr]) as $key) {
                        if ( ! is_scalar($attributes[$attr][$key])) {
                            throw new InvalidArgumentException(sprintf(
                                'Attribute %s contained non-scalar array value!',
                                $attr
                            ));
                        }
                    }
                } elseif ( ! is_scalar($attributes[$attr])) {
                    throw new InvalidArgumentException(sprintf(
                        'Attribute %s contained non-scalar value!',
                        $attr
                    ));
                }
            }

            /**
            * @var array<string, scalar|scalar[]> $attributes
            */
            $attributes = $attributes;

            return $attributes;
        }

        return [];
    }

    /**
    * @param array<int|string, mixed> $markup
    */
    public function MarkupArrayToMarkupString(
        array $markup,
        bool $xml_style = false,
        int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
        string $encoding = 'UTF-8',
        bool $double_encode = false
    ) : string {
        $this->MarkupArrayToMarkupStringValidateMarkup($markup);
        $attributes = $this->MarkupArrayToMarkupStringValidateMarkupAttributes($markup);

        /**
        * @var string $element
        */
        $element = $markup['!element'];

        $out = '<' . $element;

        foreach ($attributes as $attr => $val) {
            if (false === $val) {
                continue;
            } elseif (is_array($val)) {
                $val = implode(' ', array_map('strval', $val));
            }
            $out .= ' ' . htmlentities($attr, $flags, $encoding, $double_encode);

            if (true !== $val) {
                $out .=
                    '="' .
                    htmlentities((string) $val, (int) ($flags ^ ENT_HTML5), $encoding, false) .
                    '"';
            }
        }

        if (
            ( ! isset($markup['!content']) || empty($markup['!content'])) &&
            in_array($element, self::SELF_CLOSING_ELEMENTS, true)
        ) {
            if ($xml_style) {
                $out .= '/';
            }
            $out .= '>';
        } else {
            $out .= '>';

            if (isset($markup['!content'])) {
                /**
                * @var array<int, scalar|array<int|string, mixed>> $markupContent
                */
                $markupContent = $markup['!content'];

                /*
                These args aren't indented like I'd normally indent them due to xdebug coverage
                */
                $out .= $this->MarkupCollectionToMarkupString(
                    $markupContent, $xml_style, $flags, $encoding, $double_encode
                );
            }

            $out .= '</' . $element . '>';
        }

        return $out;
    }

    /**
    * @param array<string, string[]> $excludeElements
    * @param array<string, string[]> $keepElements
    * @param array<int, string> $generalAttrWhitelist
    *
    * @return array<int|string, mixed>
    */
    public function MarkupStringToMarkupArray(
        string $markup,
        array $excludeElements = [],
        array $keepElements = [],
        array $generalAttrWhitelist = []
    ) : array {
        $doc = new HTML5();
        $frag = $doc->loadHTMLFragment($markup);

        $out = [];

        $i = 0;
        while (($node = $frag->childNodes->item($i++)) instanceof DOMNode) {
            /*
            These args aren't indented like I'd normally indent them due to xdebug coverage
            */
            $markupArray = $this->NodeToMarkupArray(
                $node, $excludeElements, $keepElements, $generalAttrWhitelist
            );

            if ( ! isset($markupArray['!element'])) {
                $out = array_merge($out, $markupArray);
            } else {
                $out[] = $markupArray;
            }
        }

        return $out;
    }

    /**
    * @param array<string, string[]> $excludeElements
    * @param array<string, string[]> $keepElements
    * @param array<int, string> $generalAttrWhitelist
    *
    * @return array<int|string, mixed>
    */
    public function ElementNodeToMarkupArray(
        DOMElement $node,
        array $excludeElements = [],
        array $keepElements = [],
        array $generalAttrWhitelist = []
    ) {
        $out = [];

        if (
            (count($keepElements) > 0 && ! isset($keepElements[$node->nodeName])) ||
            isset($excludeElements[$node->nodeName])
        ) {
            $out[] = $node->textContent;

            return $out;
        }
        $out['!element'] = $node->nodeName;
        if ($node->hasAttributes()) {
            $out['!attributes'] = [];
        }
        $i = 0;
        while (($attr = $node->attributes->item($i++)) instanceof DOMAttr) {
            if (
                (
                    isset($keepElements[$node->nodeName]) &&
                    ! in_array($attr->name, $keepElements[$node->nodeName], true)
                ) ||
                (
                    count($generalAttrWhitelist) > 0 &&
                    ! in_array($attr->name, $generalAttrWhitelist, true)
                )
            ) {
                continue;
            }
            $out['!attributes'][$attr->name] = $attr->value;

            if (in_array($attr->name, self::BOOLEAN_ELEMENT_ATTRIBUTES, true)) {
                $out['!attributes'][$attr->name] = '' === $attr->value;
            }
        }
        if ($node->hasChildNodes()) {
            $out['!content'] = [];
            $i = 0;
            while (($child = $node->childNodes->item($i++)) instanceof DOMNode) {
                /*
                These args aren't indented like I'd normally indent them due to xdebug coverage
                */
                $childOut = $this->NodeToMarkupArray(
                    $child, $excludeElements, $keepElements, $generalAttrWhitelist
                );

                if ( ! isset($childOut['!element'])) {
                    $out['!content'] = array_merge($out['!content'], $childOut);
                } else {
                    $out['!content'][] = $childOut;
                }
            }
        }

        return $out;
    }

    /**
    * @param array<string, string[]> $excludeElements
    * @param array<string, string[]> $keepElements
    * @param array<int, string> $generalAttrWhitelist
    *
    * @return array<int|string, mixed>
    */
    public function NodeToMarkupArray(
        DOMNode $node,
        array $excludeElements = [],
        array $keepElements = [],
        array $generalAttrWhitelist = []
    ) : array {
        $out = [];

        switch ($node->nodeType) {
            case XML_ELEMENT_NODE:
                /**
                * @var DOMElement $node
                */
                $node = $node;

                $out = $this->ElementNodeToMarkupArray(
                    $node,
                    $excludeElements,
                    $keepElements,
                    $generalAttrWhitelist
                );
            break;
            case XML_TEXT_NODE:
                if ($node instanceof DOMText) {
                    $out[] = $node->wholeText;
                }
            break;
            default:
                throw new InvalidArgumentException(sprintf(
                    'Node type not supported! (%s)',
                    get_class($node)
                ));
        }

        if (isset($out['!attributes']) && empty($out['!attributes'])) {
            unset($out['!attributes']);
        }

        return $out;
    }
}
