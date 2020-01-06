<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup;

use InvalidArgumentException;

final class MarkupValidator
{
	const BOOL_IN_ARRAY_STRICT = true;

	public static function ValidateMarkup(array $markup) : void
	{
		self::MaybeThrowWhenValidatingMarkup($markup);

		foreach (array_keys($markup) as $k) {
			if ( ! in_array($k, Markup::SUPPORTED_ARRAY_ATTRIBUTES, self::BOOL_IN_ARRAY_STRICT)) {
				throw new InvalidArgumentException(sprintf('Unsupported array key! (%s)', $k));
			}
		}
	}

	/**
	* @param array{!element:string, !attributes?:array<string, scalar|array<int, scalar>>} $markup
	*
	* @return array<string, scalar|scalar[]>
	*/
	public static function ValidateMarkupAttributes(array $markup) : array
	{
		self::ValidateMarkup($markup);

		if (isset($markup['!attributes'])) {
			/**
			* @var array<int|string, mixed>
			*/
			$attributes = $markup['!attributes'];

			foreach (array_keys($attributes) as $attr) {
				$attr = self::ValidateMarkupAttributeName($attr);
				self::ValidateMarkupAttributeValue($attr, $attributes[$attr]);
			}

			/**
			* @var array<string, scalar|scalar[]>
			*/
			$attributes = $attributes;

			return $attributes;
		}

		return [];
	}

	public static function MaybeThrowWhenValidatingMarkup(array $markup) : void
	{
		if ( ! array_key_exists('!element', $markup)) {
			throw new InvalidArgumentException('Element not specified!');
		} elseif ( ! is_string($markup['!element'])) {
			throw new InvalidArgumentException('Element not specified as string!');
		} elseif (preg_match(Markup::REGEX_ELEMENT_NAME, $markup['!element']) < 1) {
			throw new InvalidArgumentException('Element not valid! (' . $markup['!element'] . ')');
		} elseif (isset($markup['!content'])) {
			self::ValidateContent($markup['!content']);
		}
	}

	/**
	* @param mixed $attr
	*/
	public static function ValidateMarkupAttributeName($attr) : string
	{
		if ( ! is_string($attr)) {
			throw new InvalidArgumentException('Attribute keys must be strings!');
		} elseif (preg_match(Markup::REGEX_ATTRIBUTE_NAME, $attr) < 1) {
			throw new InvalidArgumentException(sprintf('Attribute name invalid! (%s)', $attr));
		}

		return $attr;
	}

	public static function ValidateMarkupAttributeArrayValue(string $attr, array $value) : void
	{
		/**
		* @var array<int, int|string>
		*/
		$valueKeys = array_keys($value);

		foreach ($valueKeys as $key) {
			if ( ! is_scalar($value[$key])) {
				throw new InvalidArgumentException(sprintf(
					'Attribute %s contained a non-scalar array value!',
					$attr
				));
			}
		}
	}

	/**
	* @param mixed $value
	*/
	public static function ValidateMarkupAttributeValue(string $attr, $value) : void
	{
		if (is_array($value)) {
			static::ValidateMarkupAttributeArrayValue($attr, $value);
		} elseif ( ! is_scalar($value)) {
			throw new InvalidArgumentException(sprintf(
				'Attribute %s contained a non-scalar value!',
				$attr
			));
		}
	}

	/**
	* @param mixed $markupContent
	*/
	public static function ValidateContent($markupContent) : void
	{
		if ( ! is_array($markupContent)) {
			throw new InvalidArgumentException('Element content must be specified as an array!');
		}

		/**
		* @var array<int|string, mixed>
		*/
		$markupContent = $markupContent;

		foreach (array_keys($markupContent) as $key) {
			if ( ! is_scalar($markupContent[$key]) && ! is_array($markupContent[$key])) {
				throw new InvalidArgumentException('Element content must be scalar or an array!');
			}
		}
	}
}
