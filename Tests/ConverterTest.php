<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup\Tests;

use function array_merge;
use BadMethodCallException;
use Generator;
use PHPUnit\Framework\TestCase;
use SignpostMarv\DaftMarkup\Markup;

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
            [
                '<a-b-c-d data-efg_hij="klm"></a-b-c-d>',
                [
                    [
                        '!element' => 'a-b-c-d',
                        '!attributes' => [
                            'data-efg_hij' => 'klm',
                        ],
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
    * @psalm-return Generator<int, array{0:class-string<Markup>, 1:mixed[], 2:string, 3:array<int, scalar|array{!element:string, !attributes:array<string, scalar|array<int, scalar>>, !content?:array<int, scalar|array{!element:string}>}>, 4:bool, 5:int, 6:string, 7:bool}, mixed, void>
    */
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
            * @var string
            * @var mixed[] $ctorargs
            */
            list($class, $ctorargs) = $markupArgs;

            foreach ($this->dataProviderMarkupArrayToMarkupString() as $v) {
                /**
                * @var array{0:class-string<Markup>, 1:mixed[], 2:string, 3:array<int, scalar|array{!element:string, !attributes:array<string, scalar|array<int, scalar>>, !content?:array<int, scalar|array{!element:string}>}>, 4:bool, 5:int, 6:string, 7:bool}
                */
                $out = array_merge([$class, $ctorargs], $v);

                yield $out;
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<Markup>, 1:mixed[], 2:array, 3:string, 4:array<string, string[]>, 5:array<string, string[]>, 6:array<int, string>}, mixed, void>
    */
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
            * @var string
            * @var mixed[] $ctorargs
            */
            list($class, $ctorargs) = $markupArgs;

            foreach ($this->dataProviderMarkupStringToMarkupArray() as $v) {
                /**
                * @var array{0:class-string<Markup>, 1:mixed[], 2:array, 3:string, 4:array<string, string[]>, 5:array<string, string[]>, 6:array<int, string>}
                */
                $out = array_merge(
                    [$class, $ctorargs],
                    is_array($v) ? $v : [$v]
                );

                yield $out;
            }
        }
    }

    /**
    * @param class-string<Markup> $class,
    * @param array<int, scalar|array{!element:string, !attributes:array<string, scalar|array<int, scalar>>, !content?:array<int, scalar|array{!element:string}>}> $markup
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
        /**
        * @var Markup
        */
        $converter = 0 === count($ctorargs) ? new $class() : new $class(...$ctorargs);
        static::assertSame(
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
    * @param class-string<Markup> $class,
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
        /**
        * @var Markup
        */
        $converter = 0 === count($ctorargs) ? new $class() : new $class(...$ctorargs);
        static::assertSame(
            $expected,
            $converter->MarkupStringToMarkupArray(
                $markup,
                $excludeElements,
                $keepElements,
                $generalAttrWhitelist
            )
        );
    }
}
