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

class MarkupUtilities
{
	const COUNT_NON_EMPTY = 0;

	const BOOL_IN_ARRAY_STRICT = true;

	const OVERRIDE_BOOL_DISABLE_DOUBLE_ENCODE = false;

	const BOOLEAN_ELEMENT_ATTRIBUTES = [
		'contenteditable',
		'draggable',
		'hidden',
		'itemscope',
		'spellcheck',
	];

	/**
	 * @param array{!element:string, !attributes?:array<string, scalar|list<scalar>>, !content?:list<scalar|array{!element:string}>} $out
	 *
	 * @return array{!element:string, !attributes?:array<string, scalar|list<scalar>>, !content?:list<scalar|array{!element:string}>}
	 */
	public static function NodeToMarkupArrayStripEmptyAttributes(array $out) : array
	{
		if (
			isset($out['!attributes']) &&
			[] === $out['!attributes']
		) {
			unset($out['!attributes']);
		}

		return $out;
	}

	/**
	 * @param array<string, scalar|scalar[]> $attributes
	 */
	public static function MarkupAttributesArrayToMarkupString(
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
					htmlentities(
						(string) $val,
						($flags ^ ENT_HTML5),
						$encoding,
						self::OVERRIDE_BOOL_DISABLE_DOUBLE_ENCODE
					) .
					'"';
			}
		}

		return $out;
	}

	/**
	 * @param array<string, string[]> $keepElements
	 * @param list<string> $generalAttrWhitelist
	 *
	 * @return list<DOMAttr>
	 */
	public static function FilteredArrayFromDOMNamedNodeMap(
		DOMElement $node,
		DOMNamedNodeMap $attributes,
		array $keepElements = [],
		array $generalAttrWhitelist = []
	) : array {
		return array_values(array_filter(
			static::FilterDOMNamedNodeMapToAttrs($attributes),
			static function (DOMAttr $attr) use ($node, $keepElements, $generalAttrWhitelist) : bool {
				return static::FilterDOMAttr($node, $attr, $keepElements, $generalAttrWhitelist);
			}
		));
	}

	/**
	 * @param array<string, string[]> $keepElements
	 * @param list<string> $generalAttrWhitelist
	 *
	 * @return array<string, scalar|list<scalar>>
	 */
	public static function ObtainAttributesFromDOMNamedNodeMap(
		DOMElement $node,
		DOMNamedNodeMap $attributes,
		array $keepElements = [],
		array $generalAttrWhitelist = []
	) : array {
		/**
		 * @var array<string, scalar>
		 */
		$out = array_reduce(
			self::FilteredArrayFromDOMNamedNodeMap(
				$node,
				$attributes,
				$keepElements,
				$generalAttrWhitelist
			),
			static function (array $out, DOMAttr $attr) : array {
				$out[$attr->name] = $attr->value;

				if (
					in_array(
						$attr->name,
						self::BOOLEAN_ELEMENT_ATTRIBUTES,
						self::BOOL_IN_ARRAY_STRICT
					)
				) {
					$out[$attr->name] = '' === $attr->value;
				}

				return $out;
			},
			[]
		);

		return $out;
	}

	/**
	 * @return DOMAttr[]
	 */
	protected static function FilterDOMNamedNodeMapToAttrs(DOMNamedNodeMap $attributes) : array
	{
		return array_filter(iterator_to_array($attributes), static function (DOMNode $attr) : bool {
			return $attr instanceof DOMAttr;
		});
	}

	/**
	 * @param array<string, string[]> $keepElements
	 * @param list<string> $generalAttrWhitelist
	 */
	protected static function FilterDOMAttr(
		DOMElement $element,
		DOMAttr $attr,
		array $keepElements,
		array $generalAttrWhitelist
	) : bool {
		return
			! (
				static::FilterDOMAttrKeepElement($element, $attr, $keepElements) ||
				static::FilterDOMAttrGeneralAttrWhitelist($attr, $generalAttrWhitelist)
			);
	}

	/**
	 * @param array<string, string[]> $keepElements
	 */
	protected static function FilterDOMAttrKeepElement(
		DOMElement $node,
		DOMAttr $attr,
		array $keepElements
	) : bool {
		return
			isset($keepElements[$node->nodeName]) &&
			! in_array($attr->name, $keepElements[$node->nodeName], self::BOOL_IN_ARRAY_STRICT);
	}

	protected static function FilterDOMAttrGeneralAttrWhitelist(
		DOMAttr $attr,
		array $generalAttrWhitelist
	) : bool {
		return
			count($generalAttrWhitelist) > self::COUNT_NON_EMPTY &&
			! in_array($attr->name, $generalAttrWhitelist, self::BOOL_IN_ARRAY_STRICT);
	}
}
