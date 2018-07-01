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

        $out .= MarkupUtilities::MarkupAttributesArrayToMarkupString(
            $attrs,
            $flags,
            $encoding,
            $double
        );
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

        return $this->ElementNodeToMarkupArrayIfPassedFilter(
            $node,
            $out,
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

        return MarkupUtilities::NodeToMarkupArrayStripEmptyAttributes($out);
    }

    /**
    * @param array<int|string, mixed> $out
    * @param array<string, string[]> $excludeElements
    * @param array<string, string[]> $keepElements
    * @param array<int, string> $generalAttrWhitelist
    *
    * @return array<int|string, mixed>
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
