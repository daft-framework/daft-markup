<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup;

use DOMAttr;
use DOMElement;
use DOMNamedNodeMap;
use DOMNode;
use DOMNodeList;
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
        '/^(?:[a-z]+[a-z0-9_]*(?:\-[a-z0-9_]+)*(?:\:[a-z]+[a-z0-9_]*(?:\-[a-z0-9_]+)*){0,1})$/';

    public function MarkupCollectionToMarkupString(
        array $markupContent,
        bool $xml_style = false,
        int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
        string $encoding = 'UTF-8',
        bool $double_encode = false
    ) : string {
        $out = '';

        /**
        * @var array<int, scalar|array<int|string, mixed>> $markupContent
        */
        $markupContent = array_filter($markupContent, [$this, 'MarkupCollectionFilter']);

        foreach ($markupContent as $content) {
            if (is_array($content)) {
                $out .= $this->MarkupArrayToMarkupString(
                    $content,
                    $xml_style,
                    $flags,
                    $encoding,
                    $double_encode
                );
            } else {
                $out .= htmlentities((string) $content, $flags, $encoding, $double_encode);
            }
        }

        return $out;
    }

    /**
    * @param array<int|string, mixed> $markup
    */
    public function MarkupArrayToMarkupString(
        array $markup,
        bool $xml_style = false,
        int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
        string $encoding = 'UTF-8',
        bool $double = false
    ) : string {
        $attrs = MarkupValidator::ValidateMarkupAttributes($markup);

        /**
        * @var string $element
        */
        $element = $markup['!element'];

        $out = '<' . $element;

        $out .= $this->MarkupAttributesArrayToMarkupString($attrs, $flags, $encoding, $double);
        $out .= $this->MarkupArrayContentToMarkupString(
            $element, ((array) ($markup['!content'] ?? [])), $xml_style, $flags, $encoding, $double
        );

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
        /**
        * @var DOMNode|null $node
        */
        foreach ($frag->childNodes as $node) {
            if ( ! ($node instanceof DOMNode)) {
                continue;
            }
            $markupArray = $this->NodeToMarkupArray(
                $node,
                $excludeElements,
                $keepElements,
                $generalAttrWhitelist
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
            $out['!attributes'] = $this->ObtainAttributesFromDOMNamedNodeMap(
                $node,
                $node->attributes,
                $keepElements,
                $generalAttrWhitelist
            );
        }
        if ($node->hasChildNodes()) {
            $out['!content'] = $this->NodeListToContent(
                $node->childNodes,
                $excludeElements,
                $keepElements,
                $generalAttrWhitelist
            );
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

        if (
            isset($out['!attributes']) &&
            (
                ! is_array($out['!attributes']) ||
                [] === $out['!attributes']
            )
        ) {
            unset($out['!attributes']);
        }

        return $out;
    }

    protected function MarkupArrayContentToMarkupString(
        string $element,
        array $content,
        bool $xml_style = false,
        int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
        string $encoding = 'UTF-8',
        bool $double = false
    ) : string {
        $emptyContent = [] === $content;
        $out = '';

        if ($emptyContent && in_array($element, self::SELF_CLOSING_ELEMENTS, true)) {
            $out .= $xml_style ? '/>' : '>';
        } else {
            $out .= '>';

            if ( ! $emptyContent) {
                $out .= $this->MarkupCollectionToMarkupString(
                    $content,
                    $xml_style,
                    $flags,
                    $encoding,
                    $double
                );
            }

            $out .= '</' . $element . '>';
        }

        return $out;
    }

    /**
    * @param array<string, scalar|scalar[]> $attributes
    */
    protected function MarkupAttributesArrayToMarkupString(
        array $attributes,
        int $flags,
        string $encoding,
        bool $double_encode
    ) : string {
        $out = '';

        foreach ($attributes as $attr => $val) {
            if (false === $val) {
                continue;
            } elseif (is_array($val)) {
                $val = implode(' ', array_map('strval', $val));
            }
            $out .= ' ' . htmlentities($attr, ($flags ^ ENT_HTML5), $encoding, $double_encode);

            if (true !== $val) {
                $out .=
                    '="' .
                    htmlentities((string) $val, ($flags ^ ENT_HTML5), $encoding, false) .
                    '"';
            }
        }

        return $out;
    }

    /**
    * @param array<string, string[]> $keepElements
    * @param array<int, string> $generalAttrWhitelist
    *
    * @return DOMAttr[]
    */
    protected function FilteredArrayFromDOMNamedNodeMap(
        DOMElement $node,
        DOMNamedNodeMap $attributes,
        array $keepElements = [],
        array $generalAttrWhitelist = []
    ) : array {
        /**
        * @var DOMAttr[]
        */
        $attrs = array_filter(
            iterator_to_array($attributes),
            function (DOMNode $attr) use ($node, $keepElements, $generalAttrWhitelist) : bool {
                return
                    ($attr instanceof DOMAttr) &&
                    ! (
                        (
                            isset($keepElements[$node->nodeName]) &&
                            ! in_array($attr->name, $keepElements[$node->nodeName], true)
                        ) ||
                        (
                            count($generalAttrWhitelist) > 0 &&
                            ! in_array($attr->name, $generalAttrWhitelist, true)
                        )
                    );
            }
        );

        return $attrs;
    }

    /**
    * @param array<string, string[]> $keepElements
    * @param array<int, string> $generalAttrWhitelist
    */
    protected function ObtainAttributesFromDOMNamedNodeMap(
        DOMElement $node,
        DOMNamedNodeMap $attributes,
        array $keepElements = [],
        array $generalAttrWhitelist = []
    ) : array {
        /**
        * @var array<string, scalar> $out
        */
        $out = array_reduce(
            $this->FilteredArrayFromDOMNamedNodeMap(
                $node,
                $attributes,
                $keepElements,
                $generalAttrWhitelist
            ),
            function (array $out, DOMAttr $attr) : array {
                $out[$attr->name] = $attr->value;

                if (in_array($attr->name, self::BOOLEAN_ELEMENT_ATTRIBUTES, true)) {
                    $out[$attr->name] = '' === $attr->value;
                }

                return $out;
            },
            []
        );

        return $out;
    }

    /**
    * @param array<string, string[]> $excludeElements
    * @param array<string, string[]> $keepElements
    * @param array<int, string> $generalAttrWhitelist
    */
    protected function NodeListToContent(
        DOMNodeList $nodes,
        array $excludeElements = [],
        array $keepElements = [],
        array $generalAttrWhitelist = []
    ) : array {
        $out = [];

        $i = 0;
        /**
        * @var DOMNode|null $child
        */
        foreach ($nodes as $child) {
            if ( ! ($child instanceof DOMNode)) {
                continue;
            }
            $childOut = $this->NodeToMarkupArray(
                $child,
                $excludeElements,
                $keepElements,
                $generalAttrWhitelist
            );

            if ( ! isset($childOut['!element'])) {
                $out = array_merge($out, $childOut);
            } else {
                $out[] = $childOut;
            }
        }

        return $out;
    }

    /**
    * @param mixed $content
    */
    protected function MarkupCollectionFilter($content) : bool
    {
        return is_scalar($content) || is_array($content);
    }
}
