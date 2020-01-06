<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SignpostMarv\DaftMarkup\MarkupValidator;
use Throwable;

class ValidatorTest extends TestCase
{
	/**
	* @return list<array{0:array, 1:class-string<Throwable>, 2:string}>
	*/
	public function dataProvider_ValidateMarkup_failure() : array
	{
		return [
			[
				['!element' => 'html', '!contents' => []],
				InvalidArgumentException::class,
				'Unsupported array key! (!contents)',
			],
			[
				['!element' => 'html', 'contents' => []],
				InvalidArgumentException::class,
				'Unsupported array key! (contents)',
			],
			[
				['!element' => 'html', 'content' => []],
				InvalidArgumentException::class,
				'Unsupported array key! (content)',
			],
		];
	}

	/**
	* @param class-string<Throwable> $expected_exception
	*
	* @dataProvider dataProvider_ValidateMarkup_failure
	*/
	public function test_ValidateMarkup_failure(
		array $content,
		string $expected_exception,
		string $expected_message
	) : void {
		static::expectException($expected_exception);
		static::expectExceptionMessage($expected_message);

		MarkupValidator::ValidateMarkup($content);
	}

	/**
	* @return list<array{0:array, 1:class-string<Throwable>, 2:string}>
	*/
	public function dataProvider_MaybeThrowWhenValidatingMarkup_failure() : array
	{
		return [
			[
				[],
				InvalidArgumentException::class,
				'Element not specified!',
			],
			[
				['!element' => 1],
				InvalidArgumentException::class,
				'Element not specified as string!',
			],
			[
				['!element' => '1'],
				InvalidArgumentException::class,
				'Element not valid! (1)',
			],
		];
	}

	/**
	* @param class-string<Throwable> $expected_exception
	*
	* @dataProvider dataProvider_MaybeThrowWhenValidatingMarkup_failure
	*/
	public function test_MaybeThrowWhenValidatingMarkup_failure(
		array $content,
		string $expected_exception,
		string $expected_message
	) : void {
		static::expectException($expected_exception);
		static::expectExceptionMessage($expected_message);

		MarkupValidator::MaybeThrowWhenValidatingMarkup($content);
	}

	/**
	* @return list<array{0:mixed, 1:class-string<Throwable>, 2:string}>
	*/
	public function dataProvider_ValidateMarkupAttributeName_failure() : array
	{
		return [
			[
				null,
				InvalidArgumentException::class,
				'Attribute keys must be strings!',
			],
			[
				new \stdClass(),
				InvalidArgumentException::class,
				'Attribute keys must be strings!',
			],
			[
				[],
				InvalidArgumentException::class,
				'Attribute keys must be strings!',
			],
			[
				'',
				InvalidArgumentException::class,
				'Attribute name invalid! ()',
			],
			[
				' ',
				InvalidArgumentException::class,
				'Attribute name invalid! ( )',
			],
			[
				' id ',
				InvalidArgumentException::class,
				'Attribute name invalid! ( id )',
			],
		];
	}

	/**
	* @param mixed $attr
	* @param class-string<Throwable> $expected_exception
	*
	* @dataProvider dataProvider_ValidateMarkupAttributeName_failure
	*/
	public function test_ValidateMarkupAttributeName_failure(
		$attr,
		string $expected_exception,
		string $expected_message
	) : void {
		static::expectException($expected_exception);
		static::expectExceptionMessage($expected_message);

		MarkupValidator::ValidateMarkupAttributeName($attr);
	}

	/**
	* @return list<array{0:string, 1:array, 2:class-string<Throwable>, 3:string}>
	*/
	public function dataProvider_ValidateMarkupAttributeArrayValue_failure() : array
	{
		return [
			[
				'class',
				[null],
				InvalidArgumentException::class,
				'Attribute class contained a non-scalar array value!',
			],
			[
				'class',
				[new \stdClass()],
				InvalidArgumentException::class,
				'Attribute class contained a non-scalar array value!',
			],
			[
				'class',
				[[]],
				InvalidArgumentException::class,
				'Attribute class contained a non-scalar array value!',
			],
		];
	}

	/**
	* @param class-string<Throwable> $expected_exception
	*
	* @dataProvider dataProvider_ValidateMarkupAttributeArrayValue_failure
	*/
	public function test_ValidateMarkupAttributeArrayValue_failure(
		string $attr,
		array $value,
		string $expected_exception,
		string $expected_message
	) : void {
		static::expectException($expected_exception);
		static::expectExceptionMessage($expected_message);

		MarkupValidator::ValidateMarkupAttributeArrayValue($attr, $value);
	}

	/**
	* @return list<array{0:string, 1:mixed, 2:class-string<Throwable>, 3:string}>
	*/
	public function dataProvider_ValidateMarkupAttributeValue_failure() : array
	{
		return [
			[
				'id',
				null,
				InvalidArgumentException::class,
				'Attribute id contained a non-scalar value!',
			],
			[
				'id',
				new \stdClass(),
				InvalidArgumentException::class,
				'Attribute id contained a non-scalar value!',
			],
		];
	}

	/**
	* @param mixed $value
	* @param class-string<Throwable> $expected_exception
	*
	* @dataProvider dataProvider_ValidateMarkupAttributeValue_failure
	*/
	public function test_ValidateMarkupAttributeValue_failure(
		string $attr,
		$value,
		string $expected_exception,
		string $expected_message
	) : void {
		static::expectException($expected_exception);
		static::expectExceptionMessage($expected_message);

		MarkupValidator::ValidateMarkupAttributeValue($attr, $value);
	}

	/**
	* @return list<array{0:mixed, 1:class-string<Throwable>, 2:string}>
	*/
	public function dataProvider_ValidateContent_failure() : array
	{
		return [
			[
				'',
				InvalidArgumentException::class,
				'Element content must be specified as an array!',
			],
			[
				1,
				InvalidArgumentException::class,
				'Element content must be specified as an array!',
			],
			[
				2.3,
				InvalidArgumentException::class,
				'Element content must be specified as an array!',
			],
			[
				true,
				InvalidArgumentException::class,
				'Element content must be specified as an array!',
			],
			[
				false,
				InvalidArgumentException::class,
				'Element content must be specified as an array!',
			],
			[
				null,
				InvalidArgumentException::class,
				'Element content must be specified as an array!',
			],
			[
				new \stdClass(),
				InvalidArgumentException::class,
				'Element content must be specified as an array!',
			],
			[
				[null],
				InvalidArgumentException::class,
				'Element content must be scalar or an array!',
			],
		];
	}

	/**
	* @param mixed $markup_content
	* @param class-string<Throwable> $expected_exception
	*
	* @dataProvider dataProvider_ValidateContent_failure
	*/
	public function test_ValidateContent_failure(
		$markup_content,
		string $expected_exception,
		string $expected_message
	) : void {
		static::expectException($expected_exception);
		static::expectExceptionMessage($expected_message);

		MarkupValidator::ValidateContent($markup_content);
	}
}
