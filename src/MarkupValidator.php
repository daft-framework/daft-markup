<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup;

use InvalidArgumentException;

class MarkupValidator
{
    /**
    * @param array<int|string, mixed> $markup
    */
    public static function ValidateMarkup(array $markup) : void
    {
        if ( ! array_key_exists('!element', $markup)) {
            throw new InvalidArgumentException('Element not specified!');
        } elseif ( ! is_string($markup['!element'])) {
            throw new InvalidArgumentException('Element not specified as string!');
        } elseif (
            ! preg_match(Markup::REGEX_ELEMENT_NAME, $markup['!element'])
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
            if ( ! in_array($k, Markup::SUPPORTED_ARRAY_ATTRIBUTES, true)) {
                throw new InvalidArgumentException(sprintf('Unsupported array key! (%s)', $k));
            }
        }
    }

    /**
    * @param array<int|string, mixed> $markup
    *
    * @return array<string, scalar|scalar[]>
    */
    public static function ValidateMarkupAttributes(array $markup) : array
    {
        self::ValidateMarkup($markup);

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
                } elseif ( ! preg_match(Markup::REGEX_ATTRIBUTE_NAME, $attr)) {
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
}
