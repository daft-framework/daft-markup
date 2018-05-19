<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup\Tests;

use BadMethodCallException;
use DOMAttr;
use DOMNode;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SignpostMarv\DaftMarkup\Markup;
use function array_merge;

class ConverterTest extends TestCase
{
    const EXPECTED_MARKUP_FACTORY_ARGUMENTS = 2;
    /**
    * @var bool
    */
    protected $backupGlobals = false;

    /**
    * @var bool
    */
    protected $backupStaticAttributes = false;

    /**
    * @var bool
    */
    protected $runTestInSeparateProcess = false;

    /**
    * @return array<int, array<int, string|mixed[]>>
    */
    public function dataProviderMarkupFactory() : array
    {
        return [
            [
                Markup::class,
                [],
            ],
        ];
    }

    /**
    * @return array<int, array<int, mixed|array<int|string, mixed>|string>>
    */
    public function dataProviderMarkupArrayToMarkupString() : array
    {
        return [
            [
                '<br>',
                [
                    [
                        '!element' => 'br',
                    ],
                ],
            ],
            [
                '<br/>',
                [
                    [
                        '!element' => 'br',
                    ],
                ],
                true,
            ],
            [
                '<p></p>',
                [
                    [
                        '!element' => 'p',
                    ],
                ],
            ],
            [
                '<p></p><p><p></p></p><p></p>',
                [
                    [
                        '!element' => 'p',
                    ],
                    [
                        '!element' => 'p',
                        '!content' => [
                            [
                                '!element' => 'p',
                            ],
                        ],
                    ],
                    [
                        '!element' => 'p',
                    ],
                ],
            ],
            [
                '<h1 tabindex="0">Foo</h1>',
                [
                    [
                        '!element' => 'h1',
                        '!attributes' => [
                            'tabindex' => 0,
                        ],
                        '!content' => [
                            'Foo',
                        ],
                    ],
                ],
            ],
            [
                '<h1 contenteditable>&quot;Foo&quot;</h1>',
                [
                    [
                        '!element' => 'h1',
                        '!attributes' => [
                            'contenteditable' => true,
                        ],
                        '!content' => [
                            '"Foo"',
                        ],
                    ],
                ],
            ],
            [
                '<h1>&lt;Foo&sol;&gt;</h1>',
                [
                    [
                        '!element' => 'h1',
                        '!attributes' => [
                            'contenteditable' => false,
                        ],
                        '!content' => [
                            '<Foo/>',
                        ],
                    ],
                ],
            ],
            [
                '<h1><strong>Bar</strong></h1>',
                [
                    [
                        '!element' => 'h1',
                        '!content' => [
                            [
                                '!element' => 'strong',
                                '!content' => [
                                    'Bar',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                '<h1 style="font-weight:bolder;">&lt;Foo&sol;&gt;</h1>',
                [
                    [
                        '!element' => 'h1',
                        '!attributes' => [
                            'style' => 'font-weight:bolder;',
                        ],
                        '!content' => [
                            '<Foo/>',
                        ],
                    ],
                ],
            ],
            [
                '<h1 style="font-weight:bolder;">&lt;Foo&sol;&gt;</h1>',
                [
                    [
                        '!element' => 'h1',
                        '!attributes' => [
                            'style' => [
                                'font-weight:bolder;',
                            ],
                        ],
                        '!content' => [
                            '<Foo/>',
                        ],
                    ],
                ],
            ],
            [
                '<h1 style="font-weight:bolder; font-style:italic;">&lt;Foo&sol;&gt;</h1>',
                [
                    [
                        '!element' => 'h1',
                        '!attributes' => [
                            'style' => [
                                'font-weight:bolder;',
                                'font-style:italic;',
                            ],
                        ],
                        '!content' => [
                            '<Foo/>',
                        ],
                    ],
                ],
            ],
            [
                '&amp;',
                [
                    '&',
                ],
            ],
            [
                '&amp;',
                [
                    '&amp;',
                ],
            ],
            [
                '<a-b-c-d></a-b-c-d>',
                [
                    [
                        '!element' => 'a-b-c-d',
                    ],
                ],
            ],
            [
                '<svg:path></svg:path>',
                [
                    [
                        '!element' => 'svg:path',
                    ],
                ],
            ],
        ];
    }

    /**
    * @return array<int, string|array<int, mixed>>
    */
    public function dataProviderMarkupStringToMarkupArray() : array
    {
        return [
            [
                [
                    [
                        '!element' => 'br',
                    ],
                ],
                '<br>',
            ],
            [
                [
                    [
                        '!element' => 'br',
                    ],
                ],
                '<br/>',
            ],
            [
                [
                    [
                        '!element' => 'br',
                    ],
                ],
                '<br                                                                           />',
            ],
            [
                [
                    '&',
                ],
                '&',
            ],
            [
                [
                    [
                        '!element' => 'p',
                    ],
                ],
                '<p></p>',
            ],
            [
                [
                    [
                        '!element' => 'p',
                    ],
                    [
                        '!element' => 'p',
                    ],
                    [
                        '!element' => 'p',
                    ],
                    [
                        '!element' => 'p',
                    ],
                ],
                '<p></p><p><p></p></p><p></p>',
            ],
            [
                [
                    [
                        '!element' => 'h1',
                        '!attributes' => [
                            'tabindex' => '0',
                        ],
                        '!content' => [
                            'Foo',
                        ],
                    ],
                ],
                '<h1 tabindex="0">Foo</h1>',
            ],
            [
                [
                    [
                        '!element' => 'h1',
                        '!attributes' => [
                            'contenteditable' => true,
                        ],
                        '!content' => [
                            '"Foo"',
                        ],
                    ],
                ],
                '<h1 contenteditable>&quot;Foo&quot;</h1>',
            ],
            [
                [
                    [
                        '!element' => 'h1',
                        '!content' => [
                            '<Foo/>',
                        ],
                    ],
                ],
                '<h1>&lt;Foo&sol;&gt;</h1>',
            ],
            [
                [
                    [
                        '!element' => 'h1',
                        '!content' => [
                            [
                                '!element' => 'strong',
                                '!content' => [
                                    'Bar',
                                ],
                            ],
                        ],
                    ],
                ],
                '<h1><strong>Bar</strong></h1>',
            ],
            [
                [
                    [
                        '!element' => 'h1',
                        '!content' => [
                            'Bar',
                        ],
                    ],
                ],
                '<h1><strong>Bar</strong></h1>',
                [
                    'strong' => [],
                ],
            ],
            [
                [
                    [
                        '!element' => 'h1',
                        '!content' => [
                            '"Foo"',
                        ],
                    ],
                ],
                '<h1 contenteditable>&quot;Foo&quot;</h1>',
                [],
                [
                    'h1' => [],
                ],
            ],
            [
                [
                    [
                        '!element' => 'a-b-c-d',
                    ],
                ],
                '<a-b-c-d></a-b-c-d>',
            ],
        ];
    }

    /**
    * @return array<int, string|array>
    */
    public function dataProviderBadMarkupArrayToMarkupString() : array
    {
        return [
            [
                InvalidArgumentException::class,
                'Element not specified!',
                [],
            ],
            [
                InvalidArgumentException::class,
                'Element not specified as string!',
                [
                    '!element' => null,
                ],
            ],
            [
                InvalidArgumentException::class,
                'Element not specified as string!',
                [
                    '!element' => 1,
                ],
            ],
            [
                InvalidArgumentException::class,
                'Element not specified as string!',
                [
                    '!element' => new class() {
                        public function __toString()
                        {
                            return 'br';
                        }
                    },
                ],
            ],
            [
                InvalidArgumentException::class,
                'Element not valid! ( br)',
                [
                    '!element' => ' br',
                ],
            ],
            [
                InvalidArgumentException::class,
                'Element not valid! (br-)',
                [
                    '!element' => 'br-',
                ],
            ],
            [
                InvalidArgumentException::class,
                'Attributes not specified as an array!',
                [
                    '!element' => 'br',
                    '!attributes' => '',
                ],
            ],
            [
                InvalidArgumentException::class,
                'Attribute keys must be strings!',
                [
                    '!element' => 'br',
                    '!attributes' => [
                        true,
                    ],
                ],
            ],
            [
                InvalidArgumentException::class,
                'Attribute keys must be strings!',
                [
                    '!element' => 'br',
                    '!attributes' => [
                        'title' => 'foo',
                        [
                            'color:red;',
                        ],
                    ],
                ],
            ],
            [
                InvalidArgumentException::class,
                'Attribute name invalid! ( title )',
                [
                    '!element' => 'br',
                    '!attributes' => [
                        ' title ' => 'foo',
                    ],
                ],
            ],
            [
                InvalidArgumentException::class,
                'Attribute style contained non-scalar array value!',
                [
                    '!element' => 'br',
                    '!attributes' => [
                        'style' => [
                            ['color:red;'],
                        ],
                    ],
                ],
            ],
            [
                InvalidArgumentException::class,
                'Attribute title contained non-scalar value!',
                [
                    '!element' => 'br',
                    '!attributes' => [
                        'title' => null,
                    ],
                ],
            ],
            [
                InvalidArgumentException::class,
                'Element content must be specified as an array!',
                [
                    '!element' => 'br',
                    '!content' => 'foo',
                ],
            ],
            [
                InvalidArgumentException::class,
                'Element content must be scalar or an array!',
                [
                    '!element' => 'br',
                    '!content' => [
                        'foo',
                        null,
                    ],
                ],
            ],
            [
                InvalidArgumentException::class,
                'Unsupported array key! (!attrs)',
                [
                    '!element' => 'br',
                    '!attrs' => [],
                ],
            ],
        ];
    }

    /**
    * @return array<int, array<string|array>>
    */
    public function dataProviderBadNodeToMarkupArray() : array
    {
        return [
            [
                InvalidArgumentException::class,
                'Node type not supported! (' . DOMAttr::class . ')',
                DOMAttr::class,
                [
                    'title',
                    'foo',
                ],
            ],
        ];
    }

    public function dataProviderMarkupFactoryPlusMarkupArrayToMarkupString() : Generator
    {
        foreach ($this->dataProviderMarkupFactory() as $k => $markupArgs) {
            if (
                self::EXPECTED_MARKUP_FACTORY_ARGUMENTS !== count($markupArgs) ||
                ! isset($markupArgs[0], $markupArgs[1])
            ) {
                throw new BadMethodCallException(sprintf(
                    '%s::dataProviderMarkupFactory() contains insufficient args at index %s',
                    static::class,
                    $k
                ));
            } elseif ( ! is_string($markupArgs[0])) {
                throw new BadMethodCallException(sprintf(
                    '%s::dataProviderMarkupFactory() contains an invalid class value at index %s',
                    static::class,
                    $k
                ));
            } elseif ( ! is_array($markupArgs[1])) {
                throw new BadMethodCallException(sprintf(
                    '%s::dataProviderMarkupFactory() contains an invalid constructor args at index %s',
                    static::class,
                    $k
                ));
            }

            /**
            * @var string $class
            * @var mixed[] $ctorargs
            */
            list($class, $ctorargs) = $markupArgs;

            foreach ($this->dataProviderMarkupArrayToMarkupString() as $markupArgs) {
                yield array_merge([$class, $ctorargs], $markupArgs);
            }
        }
    }

    public function dataProviderMarkupFactoryPlusMarkupStringToMarkupArray() : Generator
    {
        foreach ($this->dataProviderMarkupFactory() as $k => $markupArgs) {
            if (
                self::EXPECTED_MARKUP_FACTORY_ARGUMENTS !== count($markupArgs) ||
                ! isset($markupArgs[0], $markupArgs[1])
            ) {
                throw new BadMethodCallException(sprintf(
                    '%s::dataProviderMarkupFactory() contains insufficient args at index %s',
                    static::class,
                    $k
                ));
            } elseif ( ! is_string($markupArgs[0])) {
                throw new BadMethodCallException(sprintf(
                    '%s::dataProviderMarkupFactory() contains an invalid class value at index %s',
                    static::class,
                    $k
                ));
            } elseif ( ! is_array($markupArgs[1])) {
                throw new BadMethodCallException(sprintf(
                    '%s::dataProviderMarkupFactory() contains an invalid constructor args at index %s',
                    static::class,
                    $k
                ));
            }

            /**
            * @var string $class
            * @var mixed[] $ctorargs
            */
            list($class, $ctorargs) = $markupArgs;

            foreach ($this->dataProviderMarkupStringToMarkupArray() as $markupArgs) {
                yield array_merge(
                    [$class, $ctorargs],
                    is_array($markupArgs) ? $markupArgs : [$markupArgs]
                );
            }
        }
    }

    public function dataProviderMarkupFactoryPlusBadMarkupArrayToMarkupString() : Generator
    {
        foreach ($this->dataProviderMarkupFactory() as $k => $markupArgs) {
            if (
                self::EXPECTED_MARKUP_FACTORY_ARGUMENTS !== count($markupArgs) ||
                ! isset($markupArgs[0], $markupArgs[1])
            ) {
                throw new BadMethodCallException(sprintf(
                    '%s::dataProviderMarkupFactory() contains insufficient args at index %s',
                    static::class,
                    $k
                ));
            } elseif ( ! is_string($markupArgs[0])) {
                throw new BadMethodCallException(sprintf(
                    '%s::dataProviderMarkupFactory() contains an invalid class value at index %s',
                    static::class,
                    $k
                ));
            } elseif ( ! is_array($markupArgs[1])) {
                throw new BadMethodCallException(sprintf(
                    '%s::dataProviderMarkupFactory() contains an invalid constructor args at index %s',
                    static::class,
                    $k
                ));
            }

            /**
            * @var string $class
            * @var mixed[] $ctorargs
            */
            list($class, $ctorargs) = $markupArgs;

            foreach ($this->dataProviderBadMarkupArrayToMarkupString() as $markupArgs) {
                yield array_merge([$class, $ctorargs], $markupArgs);
            }
        }
    }

    public function dataProviderMarkupFactoryPlusBadNodeToMarkupArray() : Generator
    {
        foreach ($this->dataProviderMarkupFactory() as $k => $markupArgs) {
            if (
                self::EXPECTED_MARKUP_FACTORY_ARGUMENTS !== count($markupArgs) ||
                ! isset($markupArgs[0], $markupArgs[1])
            ) {
                throw new BadMethodCallException(sprintf(
                    '%s::dataProviderMarkupFactory() contains insufficient args at index %s',
                    static::class,
                    $k
                ));
            } elseif ( ! is_string($markupArgs[0])) {
                throw new BadMethodCallException(sprintf(
                    '%s::dataProviderMarkupFactory() contains an invalid class value at index %s',
                    static::class,
                    $k
                ));
            } elseif ( ! is_array($markupArgs[1])) {
                throw new BadMethodCallException(sprintf(
                    '%s::dataProviderMarkupFactory() contains an invalid constructor args at index %s',
                    static::class,
                    $k
                ));
            }

            /**
            * @var string $class
            * @var mixed[] $ctorargs
            */
            list($class, $ctorargs) = $markupArgs;

            foreach ($this->dataProviderBadNodeToMarkupArray() as $markupArgs) {
                yield array_merge([$class, $ctorargs], $markupArgs);
            }
        }
    }

    /**
    * @param array<int, scalar|array<int|string, mixed>> $markup
    *
    * @dataProvider dataProviderMarkupFactoryPlusMarkupArrayToMarkupString
    */
    public function testMarkupArrayToMarkupString(
        string $class,
        array $ctorargs,
        string $expected,
        array $markup,
        bool $xml_style = false,
        int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
        string $encoding = 'UTF-8',
        bool $double_encode = false
    ) : void {
        if ( ! is_a($class, Markup::class, true)) {
            throw new BadMethodCallException(sprintf(
                'Argument 1 passed to %s must be an implementation of %s',
                __METHOD__,
                Markup::class
            ));
        }

        /**
        * @var Markup $converter
        */
        $converter = 0 === count($ctorargs) ? new $class() : new $class(...$ctorargs);
        $this->assertSame(
            $expected,
            $converter->MarkupCollectionToMarkupString(
                $markup,
                $xml_style,
                $flags,
                $encoding,
                $double_encode
            )
        );
    }

    /**
    * @param array<string, string[]> $excludeElements
    * @param array<string, string[]> $keepElements
    * @param array<int, string> $generalAttrWhitelist
    *
    * @dataProvider dataProviderMarkupFactoryPlusMarkupStringToMarkupArray
    */
    public function testMarkupStringToMarkupArray(
        string $class,
        array $ctorargs,
        array $expected,
        string $markup,
        array $excludeElements = [],
        array $keepElements = [],
        array $generalAttrWhitelist = []
    ) : void {
        if ( ! is_a($class, Markup::class, true)) {
            throw new BadMethodCallException(sprintf(
                'Argument 1 passed to %s must be an implementation of %s',
                __METHOD__,
                Markup::class
            ));
        }

        /**
        * @var Markup $converter
        */
        $converter = 0 === count($ctorargs) ? new $class() : new $class(...$ctorargs);
        $this->assertSame(
            $expected,
            $converter->MarkupStringToMarkupArray(
                $markup,
                $excludeElements,
                $keepElements,
                $generalAttrWhitelist
            )
        );
    }

    /**
    * @param array<int, scalar|array<int|string, mixed>> $markup
    *
    * @dataProvider dataProviderMarkupFactoryPlusBadMarkupArrayToMarkupString
    */
    public function testBadMarkupArrayToMarkupString(
        string $class,
        array $ctorargs,
        string $expectedExceptionClass,
        string $expectedExceptionMessage,
        array $markup,
        bool $xml_style = false,
        int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
        string $encoding = 'UTF-8',
        bool $double_encode = false
    ) : void {
        if ( ! is_a($class, Markup::class, true)) {
            throw new BadMethodCallException(sprintf(
                'Argument 1 passed to %s must be an implementation of %s',
                __METHOD__,
                Markup::class
            ));
        }

        /**
        * @var Markup $converter
        */
        $converter = 0 === count($ctorargs) ? new $class() : new $class(...$ctorargs);

        $this->expectException($expectedExceptionClass);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $converter->MarkupArrayToMarkupString(
            $markup,
            $xml_style,
            $flags,
            $encoding,
            $double_encode
        );
    }

    /**
    * @param array<string, string[]> $excludeElements
    * @param array<string, string[]> $keepElements
    * @param array<int, string> $generalAttrWhitelist
    *
    * @dataProvider dataProviderMarkupFactoryPlusBadNodeToMarkupArray
    */
    public function testBadNodeToMarkupArray(
        string $class,
        array $ctorargs,
        string $expectedExceptionClass,
        string $expectedExceptionMessage,
        string $nodeClass,
        array $nodeCtorargs,
        array $excludeElements = [],
        array $keepElements = [],
        array $generalAttrWhitelist = []
    ) : void {
        if ( ! is_a($class, Markup::class, true)) {
            throw new BadMethodCallException(sprintf(
                'Argument 1 passed to %s must be an implementation of %s',
                __METHOD__,
                Markup::class
            ));
        } elseif ( ! is_a($nodeClass, DOMNode::class, true)) {
            throw new BadMethodCallException(sprintf(
                'Argument 5 passed to %s must be an implementation of %s',
                __METHOD__,
                DOMNode::class
            ));
        }

        /**
        * @var Markup $converter
        */
        $converter = 0 === count($ctorargs) ? new $class() : new $class(...$ctorargs);

        /**
        * @var DOMNode $node
        */
        $node = 0 === count($nodeCtorargs) ? new $nodeClass() : new $nodeClass(...$nodeCtorargs);

        $this->expectException($expectedExceptionClass);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $converter->NodeToMarkupArray(
            $node,
            $excludeElements,
            $keepElements,
            $generalAttrWhitelist
        );
    }
}
