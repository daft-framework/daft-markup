<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup;

use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMText;
use InvalidArgumentException;
use Masterminds\HTML5;

class Markup
{
    const COUNT_NON_EMPTY = 0;

    const BOOL_IN_ARRAY_STRICT = true;

    const DEFAULT_BOOL_XML_STYLE = false;

    const DEFAULT_BITWISE_FLAGS = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5;

    const DEFAULT_STRING_ENCODING = 'UTF-8';

    const DEFAULT_BOOL_DOUBLE_ENCODE = false;

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

    const REGEX_ELEMENT_NAME =
        '/^(?:[a-z]+[a-z0-9]*(?:\-[a-z0-9]+)*(?:\:[a-z]+[a-z0-9]*(?:\-[a-z0-9]+)*){0,1})$/';

    const REGEX_ATTRIBUTE_NAME =
        '/^(?:[a-z]+[a-z0-9_]*(?:\-[a-z0-9_]+)*(?:\:[a-z]+[a-z0-9_]*(?:\-[a-z0-9_]+)*){0,1})$/';

    /**
    * @param array<int, scalar|array{!element:string, !attributes:array<string, scalar|array<int, scalar>>, !content?:array<int, scalar|array{!element:string}>}> $markupContent
    */
    public function MarkupCollectionToMarkupString(
        array $markupContent,
        bool $xml_style = self::DEFAULT_BOOL_XML_STYLE,
        int $flags = self::DEFAULT_BITWISE_FLAGS,
        string $encoding = self::DEFAULT_STRING_ENCODING,
        bool $double_encode = self::DEFAULT_BOOL_DOUBLE_ENCODE
    ) : string {
        $out = '';

        /**
        * @var array<int, scalar|array{!element:string, !attributes:array<string, scalar|array<int, scalar>>, !content?:array<int, scalar|array{!element:string}>}>
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
    * @param array{!element:string, !attributes:array<string, scalar|array<int, scalar>>, !content?:array<int, scalar|array{!element:string}>} $markup
    */
    public function MarkupArrayToMarkupString(
        array $markup,
        bool $xml_style = self::DEFAULT_BOOL_XML_STYLE,
        int $flags = self::DEFAULT_BITWISE_FLAGS,
        string $encoding = self::DEFAULT_STRING_ENCODING,
        bool $double = self::DEFAULT_BOOL_DOUBLE_ENCODE
    ) : string {
        $attrs = MarkupValidator::ValidateMarkupAttributes($markup);

        /**
        * @var string
        */
        $element = $markup['!element'];

        $out = '<' . $element;

        $out .= MarkupUtilities::MarkupAttributesArrayToMarkupString(
            $attrs,
            $flags,
            $encoding,
            $double
        );

        /**
        * @var array<int, scalar|array{!element:string, !attributes:array<string, scalar|array<int, scalar>>, !content?:array<int, scalar|array{!element:string}>}>
        */
        $markup_content = $markup['!content'] ?? [];

        $out .= $this->MarkupArrayContentToMarkupString(
            $element, $markup_content, $xml_style, $flags, $encoding, $double
        );

        return $out;
    }

    /**
    * @param array<string, string[]> $excludeElements
    * @param array<string, string[]> $keepElements
    * @param array<int, string> $generalAttrWhitelist
    *
    * @return array<int, array{!element:string, !attributes:array<string, scalar|array<int, scalar>>, !content?:array<int, scalar|array{!element:string}>}>
    */
    public function MarkupStringToMarkupArray(
        string $markup,
        array $excludeElements = [],
        array $keepElements = [],
        array $generalAttrWhitelist = []
    ) : array {
        $doc = new HTML5();
        $frag = $doc->loadHTMLFragment($markup);

        return $this->NodeListToContent(
            $frag->childNodes,
            $excludeElements,
            $keepElements,
            $generalAttrWhitelist
        );
    }

    /**
    * @param array<string, string[]> $excludeElements
    * @param array<string, string[]> $keepElements
    * @param array<int, string> $generalAttrWhitelist
    *
    * @return array{0:string}|array{!element:string, !attributes:array<string, scalar|array<int, scalar>>, !content?:array<int, scalar|array{!element:string}>}
    */
    public function ElementNodeToMarkupArray(
        DOMElement $node,
        array $excludeElements = [],
        array $keepElements = [],
        array $generalAttrWhitelist = []
    ) {
        if (
            (
                count($keepElements) > self::COUNT_NON_EMPTY &&
                ! isset($keepElements[$node->nodeName])
            ) ||
            isset($excludeElements[$node->nodeName])
        ) {
            return [$node->textContent];
        }

        /**
        * @var array{!element:string}
        */
        $out = [
            '!element' => $node->nodeName,
        ];

        return $this->ElementNodeToMarkupArrayIfPassedFilter(
            $node,
            $out,
            $excludeElements,
            $keepElements,
            $generalAttrWhitelist
        );
    }

    /**
    * @param DOMElement|DOMText $node
    * @param array<string, string[]> $excludeElements
    * @param array<string, string[]> $keepElements
    * @param array<int, string> $generalAttrWhitelist
    *
    * @return array{0:string}|array{!element:string, !attributes?:array<string, scalar|array<int, scalar>>, !content?:array<int, scalar|array{!element:string}>}
    */
    public function NodeToMarkupArray(
        DOMNode $node,
        array $excludeElements = [],
        array $keepElements = [],
        array $generalAttrWhitelist = []
    ) : array {
        if ($node instanceof DOMElement) {
            $out = $this->ElementNodeToMarkupArray(
                $node,
                $excludeElements,
                $keepElements,
                $generalAttrWhitelist
            );

            if ( ! isset($out['!element'])) {
                return $out;
            }

            return MarkupUtilities::NodeToMarkupArrayStripEmptyAttributes($out);
        }

        return [$node->wholeText];
    }

    /**
    * @param array{!element:string} $out
    * @param array<string, string[]> $excludeElements
    * @param array<string, string[]> $keepElements
    * @param array<int, string> $generalAttrWhitelist
    *
    * @return array{!element:string, !attributes?:array<string, scalar|array<int, scalar>>, !content?:array<int, scalar|array{!element:string}>}
    */
    protected function ElementNodeToMarkupArrayIfPassedFilter(
        DOMElement $node,
        array $out,
        array $excludeElements = [],
        array $keepElements = [],
        array $generalAttrWhitelist = []
    ) : array {
        if ($node->hasAttributes()) {
            $out['!attributes'] = MarkupUtilities::ObtainAttributesFromDOMNamedNodeMap(
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
    * @param array<int, scalar|array{!element:string, !attributes:array<string, scalar|array<int, scalar>>, !content?:array<int, scalar|array{!element:string}>}> $content
    */
    protected function MarkupArrayContentToMarkupString(
        string $element,
        array $content,
        bool $xml_style = self::DEFAULT_BOOL_XML_STYLE,
        int $flags = self::DEFAULT_BITWISE_FLAGS,
        string $encoding = self::DEFAULT_STRING_ENCODING,
        bool $double = self::DEFAULT_BOOL_DOUBLE_ENCODE
    ) : string {
        $emptyContent = [] === $content;
        $out = '';

        if (
            $emptyContent &&
            in_array($element, self::SELF_CLOSING_ELEMENTS, self::BOOL_IN_ARRAY_STRICT)
        ) {
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
    * @param array<string, string[]> $excludeElements
    * @param array<string, string[]> $keepElements
    * @param array<int, string> $generalAttrWhitelist
    *
    * @return array<int, array{!element:string, !attributes:array<string, scalar|array<int, scalar>>, !content?:array<int, scalar|array{!element:string}>}>
    */
    protected function NodeListToContent(
        DOMNodeList $nodes,
        array $excludeElements = [],
        array $keepElements = [],
        array $generalAttrWhitelist = []
    ) : array {
        /**
        * @var array<int, DOMElement|DOMText>
        */
        $filtered_nodes = array_filter(
            iterator_to_array($nodes),
            function (DOMNode $maybe) : bool {
                return ($maybe instanceof DOMElement) || ($maybe instanceof DOMText);
            }
        );

        /**
        * @var array<int, array{!element:string, !attributes:array<string, scalar|array<int, scalar>>, !content?:array<int, scalar|array{!element:string}>}>
        */
        $out = array_reduce(
            array_map(
                /**
                * @param DOMElement|DOMText $child
                */
                function (
                    DOMNode $child
                ) use (
                    $excludeElements,
                    $keepElements,
                    $generalAttrWhitelist
                ) : array {
                    return $this->NodeToMarkupArray(
                        $child,
                        $excludeElements,
                        $keepElements,
                        $generalAttrWhitelist
                    );
                },
                $filtered_nodes
            ),
            function (array $out, array $childOut) : array {
                if ( ! isset($childOut['!element'])) {
                    $out = array_merge($out, $childOut);
                } else {
                    $out[] = $childOut;
                }

                return $out;
            },
            []
        );

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
