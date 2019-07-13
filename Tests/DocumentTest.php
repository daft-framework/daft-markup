<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup\Tests;

use BadMethodCallException;
use Closure;
use Generator;
use PHPUnit\Framework\TestCase;
use SignpostMarv\DaftMarkup\AbstractHtmlElement;
use SignpostMarv\DaftMarkup\Html\Document;
use SignpostMarv\DaftMarkup\Markup;
use Throwable;

class DocumentTest extends TestCase
{
	/**
	* @return array<int, array<int, string|array>>
	*
	* @psalm-return array<int, array{0:class-string<Document>, 1:mixed[]}>
	*/
	public function dataProviderDocumentInstance() : array
	{
		return [
			[
				Document::class,
				[],
			],
		];
	}

	/**
	* @return array<int, array<string|mixed[]|Closure|null>>
	*
	* @psalm-return array<int, array{0:class-string<Document>, 1:mixed[], 2:array<int, scalar|array{!element:string}>|null, 3:Closure(Document):void|null, 4:string}>
	*/
	public function dataProviderDocumentToString() : array
	{
		return [
			[
				Document::class,
				[],
				null,
				function (Document $doc) : void {
					$doc->SetTitle('Test');
					$doc->asyncJs('./foo.js');
					$doc->deferJs('./bar.js', './baz.js');
					$doc->Preload('style', './foo.css', './bar.css');
					$doc->Preload('module', './bar-module.js');
					$doc->ConfigureIntegrity('./foo.js', 'example');
					$doc->ConfigureIntegrity('./foo.css', 'example-2');
					$doc->IncludeCss('./foo.css', './bar.css', './baz.css');
					$doc->ExcludeCss('./baz.css');
					$doc->CrossOrigin('anonymous', './bar.css', './baz.css', './foo.js');
					$doc->AppendMeta('description', 'Example');
					$doc->IncludeModules('./bar-module.js');
					$doc->IncludeNoModules('./bar-not-module.js');
					$doc->ExcludeJs('./baz.js');
				},
				(
					'<!DOCTYPE html>' .
					"\n" .
					'<html>' .
						'<head>' .
							'<meta charset="utf-8">' .
							'<title>Test</title>' .
							'<link rel="preload" href="./foo.css" as="style">' .
							'<link rel="preload" href="./bar.css" as="style" crossorigin="anonymous">' .
							'<link rel="modulepreload" href="./bar-module.js">' .
							'<link rel="stylesheet" href="./foo.css" integrity="example-2">' .
							'<link rel="stylesheet" href="./bar.css" crossorigin="anonymous">' .
							'<meta name="description" content="Example">' .
						'</head>' .
						'<body>' .
							'<script src="./foo.js" async crossorigin="anonymous" integrity="example"></script>' .
							'<script src="./bar.js" defer></script>' .
							'<script src="./bar-module.js" type="module"></script>' .
							'<script src="./bar-not-module.js" nomodule></script>' .
						'</body>' .
					'</html>'
				),
			],
			[
				Document::class,
				[],
				null,
				function (Document $doc) : void {
					$doc->SetMarkupConverter(new Markup());
					$doc->SetTitle('Test');
					$doc->SetTitleAttribute('Toast');
					$doc->SetLang('en');
					$doc->SetCharset('iso-8859-1');
					$doc->AppendMeta('http:Refresh', '5');
					$doc->Preload('script', './foo.js');
					$doc->ConfigureIntegrity('./foo.js', 'example');
					$doc->SetEnableIntegrityOnPreload(true);
					$doc->SetItemScope(true);
					$doc->SetHidden(true);
					$doc->SetIs('html-future');
					$doc->SetId('foo');
					$doc->SetDropzone('copy');
					$doc->SetItemId('baz');
					$doc->SetSlot('nope');
					$doc->SetItemType('http://schema.org/Thing');
					$doc->SetDir('ltr');
					$doc->SetContextMenu('bag');
					$doc->SetContentEditable(true);
					$doc->SetAutoCapitalize('off');
				},
				(
					'<!DOCTYPE html>' .
					"\n" .
					'<html autocapitalize="off" contenteditable contextmenu="bag" dir="ltr" dropzone="copy" hidden id="foo" is="html-future" itemid="baz" itemscope itemtype="http://schema.org/Thing" lang="en" slot="nope" title="Toast">' .
						'<head>' .
							'<meta charset="iso-8859-1">' .
							'<title>Test</title>' .
							'<link rel="preload" href="./foo.js" as="script" integrity="example">' .
							'<meta http-equiv="Refresh" content="5">' .
						'</head>' .
					'</html>'
				),
			],
			[
				Document::class,
				[],
				null,
				function (Document $doc, TestCase $test) : void {
					$doc->SetMarkupConverter(new Markup());
					$doc->SetTitle('Test');
					$doc->Preload('script', './foo.js');
					$doc->ConfigureIntegrity('./foo.js', 'example');

					$test::assertSame(
						[
							'Link: <./foo.js>; rel=preload; as=script',
						],
						$doc->GetPossibleHeaders()
					);
					$doc->ClearPossibleHeaderSources();
				},
				(
					'<!DOCTYPE html>' .
					"\n" .
					'<html>' .
						'<head>' .
							'<meta charset="utf-8">' .
							'<title>Test</title>' .
						'</head>' .
					'</html>'
				),
			],
			[
				Document::class,
				[],
				null,
				function (Document $doc, TestCase $test) : void {
					$doc->SetMarkupConverter(new Markup());
					$doc->SetTitle('Test');
					$doc->Preload('script', './foo.js');
					$doc->ConfigureIntegrity('./foo.js', 'example');
					$doc->SetEnableIntegrityOnPreload(true);

					$test::assertSame(
						[
							'Link: <./foo.js>; rel=preload; as=script; integrity=example',
						],
						$doc->GetPossibleHeaders()
					);

					$doc->ClearPossibleHeaderSources();
				},
				(
					'<!DOCTYPE html>' .
					"\n" .
					'<html>' .
						'<head>' .
							'<meta charset="utf-8">' .
							'<title>Test</title>' .
						'</head>' .
					'</html>'
				),
			],
			[
				Document::class,
				[],
				null,
				function (Document $doc, TestCase $test) : void {
					$doc->SetMarkupConverter(new Markup());
					$doc->SetTitle('Test');
					$doc->Preload('script', './foo.js');
					$doc->ConfigureIntegrity('./foo.js', 'example');
					$doc->SetEnableIntegrityOnPreload(true);
					$doc->ClearPossibleHeaderSources();

					$test::assertSame([], $doc->GetPossibleHeaders());
				},
				(
					'<!DOCTYPE html>' .
					"\n" .
					'<html>' .
						'<head>' .
							'<meta charset="utf-8">' .
							'<title>Test</title>' .
						'</head>' .
					'</html>'
				),
			],
			[
				Document::class,
				[],
				null,
				function (Document $doc) : void {
					$doc->SetTitle('Test');
					$doc->ApplyValueForDataAttribute('foo', 'bar');
					$doc->SetTabIndex(-1);
					$doc->SetTranslate(false);
					$doc->AppendClass('no-js');
				},
				(
					'<!DOCTYPE html>' .
					"\n" .
					'<html class="no-js" data-foo="bar" tabindex="-1" translate="no">' .
						'<head>' .
							'<meta charset="utf-8">' .
							'<title>Test</title>' .
						'</head>' .
					'</html>'
				),
			],
			[
				Document::class,
				[],
				null,
				function (Document $doc) : void {
					$doc->SetTitle('Test');
					$doc->SetDraggable(true);
				},
				(
					'<!DOCTYPE html>' .
					"\n" .
					'<html draggable="true">' .
						'<head>' .
							'<meta charset="utf-8">' .
							'<title>Test</title>' .
						'</head>' .
					'</html>'
				),
			],
			[
				Document::class,
				[],
				null,
				function (Document $doc) : void {
					$doc->SetTitle('Test');
					$doc->SetDraggable(false);
				},
				(
					'<!DOCTYPE html>' .
					"\n" .
					'<html draggable="false">' .
						'<head>' .
							'<meta charset="utf-8">' .
							'<title>Test</title>' .
						'</head>' .
					'</html>'
				),
			],
			[
				Document::class,
				[],
				null,
				function (Document $doc) : void {
					$doc->SetTitle('Test');
					$doc->SetDraggable(null);
				},
				(
					'<!DOCTYPE html>' .
					"\n" .
					'<html>' .
						'<head>' .
							'<meta charset="utf-8">' .
							'<title>Test</title>' .
						'</head>' .
					'</html>'
				),
			],
			[
				Document::class,
				[],
				null,
				function (Document $doc) : void {
					$doc->SetTitle('Test');
					$doc->SetSpellcheck(true);
				},
				(
					'<!DOCTYPE html>' .
					"\n" .
					'<html spellcheck="true">' .
						'<head>' .
							'<meta charset="utf-8">' .
							'<title>Test</title>' .
						'</head>' .
					'</html>'
				),
			],
			[
				Document::class,
				[],
				null,
				function (Document $doc) : void {
					$doc->SetTitle('Test');
					$doc->SetSpellcheck(false);
				},
				(
					'<!DOCTYPE html>' .
					"\n" .
					'<html spellcheck="false">' .
						'<head>' .
							'<meta charset="utf-8">' .
							'<title>Test</title>' .
						'</head>' .
					'</html>'
				),
			],
			[
				Document::class,
				[],
				null,
				function (Document $doc) : void {
					$doc->SetTitle('Test');
					$doc->SetSpellcheck(null);
				},
				(
					'<!DOCTYPE html>' .
					"\n" .
					'<html>' .
						'<head>' .
							'<meta charset="utf-8">' .
							'<title>Test</title>' .
						'</head>' .
					'</html>'
				),
			],
		];
	}

	/**
	* @return array<int, array<string|mixed[]|Closure|null>>
	*
	* @psalm-return array<int, array{0:class-string<Document>, 1:mixed[], 2:class-string<Throwable>, 3:string}>
	*/
	public function dataProviderBadDocumentToString() : array
	{
		return [
			[
				Document::class,
				[],
				BadMethodCallException::class,
				'Document title must not be empty!',
			],
		];
	}

	/**
	* @return string[][]
	*/
	public function dataProviderStringArrayMethodNames() : array
	{
		return [
			[
				'AccessKey',
			],
			[
				'Class',
			],
			[
				'ItemRefs',
			],
			[
				'Style',
			],
		];
	}

	/**
	* @return string[][][]
	*/
	public function dataProviderStringArrayMethodTestingValues() : array
	{
		return [
			[
				['b', 'c', 'd'], // set
				['b', 'c', 'd'], // assertSame $expected
				['a', 'e'], // append
				['b', 'c', 'd', 'a', 'e'], // assertSame $expected
				['b', 'c', 'd', 'a', 'e'], // assertSame $expected post-sort
				['a', 'b', 'c', 'd', 'e'], // assertSame $expected post-sort, post-set
			],
		];
	}

	/**
	* @psalm-return Generator<int, array{0:class-string<Document>, 1:mixed[], 2:string, 3:string[], 4:string[], 5:string[], 6:string[], 7:string[], 8:string[]}, mixed, void>
	*/
	public function dataProviderStringArrayMethods() : Generator
	{
		foreach ($this->dataProviderDocumentInstance() as $classArgs) {
			foreach ($this->dataProviderStringArrayMethodNames() as $methodNameArgs) {
				foreach ($this->dataProviderStringArrayMethodTestingValues() as $testingArgs) {
					/**
					* @psalm-var array{0:class-string<Document>, 1:mixed[], 2:string, 3:string[], 4:string[], 5:string[], 6:string[], 7:string[], 8:string[]}
					*/
					$out = array_merge($classArgs, $methodNameArgs, $testingArgs);

					yield $out;
				}
			}
		}
	}

	/**
	* @return array<int, array<int, scalar|array|null>>
	*/
	public function dataProviderDefaultValues() : array
	{
		return [
			[
				'AccessKey',
				[],
			],
			[
				'AutoCapitalize',
				null,
			],
			[
				'Class',
				[],
			],
			[
				'ContentEditable',
				false,
			],
			[
				'ContextMenu',
				null,
			],
			[
				'Dir',
				null,
			],
			[
				'Draggable',
				false,
			],
			[
				'Dropzone',
				null,
			],
			[
				'Hidden',
				false,
			],
			[
				'Id',
				null,
			],
			[
				'Is',
				null,
			],
			[
				'ItemId',
				null,
			],
			[
				'ItemRefs',
				[],
			],
			[
				'ItemScope',
				false,
			],
			[
				'ItemType',
				null,
			],
			[
				'Lang',
				null,
			],
			[
				'Slot',
				null,
			],
			[
				'Spellcheck',
				false,
			],
			[
				'Style',
				[],
			],
			[
				'TabIndex',
				null,
			],
			[
				'Title',
				'',
			],
			[
				'TitleAttribute',
				null,
			],
			[
				'Translate',
				true,
			],
		];
	}

	/**
	* @psalm-return Generator<int, array{0:class-string<AbstractHtmlElement>, 1:string, 2:scalar|null}, mixed, void>
	*/
	public function dataProviderTestDefaults() : Generator
	{
		foreach ($this->dataProviderDocumentInstance() as $classArgs) {
			foreach ($this->dataProviderDefaultValues() as $valuesArgs) {
				/**
				* @psalm-var array{0:class-string<AbstractHtmlElement>, 1:string, 2:scalar|null}
				*/
				$out = array_merge([$classArgs[0]], $valuesArgs);

				yield $out;
			}
		}
	}

	/**
	* @dataProvider dataProviderDocumentInstance
	*/
	public function testIsAbstractHtmlElement(string $class) : void
	{
		static::assertTrue(is_a($class, AbstractHtmlElement::class, true));
	}

	/**
	* @dataProvider dataProviderDocumentInstance
	*
	* @depends testIsAbstractHtmlElement
	*
	* @param class-string<AbstractHtmlElement> $class
	*/
	public function testValidElementName(string $class) : void
	{
		static::assertRegExp(Markup::REGEX_ELEMENT_NAME, (string) ($class::MarkupElementName()));
	}

	/**
	* @param class-string<Document> $class
	* @param array<int, scalar|array{!element:string}>|null $content
	*
	* @dataProvider dataProviderDocumentToString
	*
	* @depends testIsAbstractHtmlElement
	*/
	public function testDocumentToString(
		string $class,
		array $ctorargs,
		? array $content,
		? Closure $decorateDocument,
		string $expected
	) : void {
		$doc = $this->AbstractHtmlElementFromCtorArgs($class, $ctorargs);

		if ($doc instanceof Document) {
			$doc->ApplyValueForDataAttribute('foo', 'bar');
			static::assertSame('bar', $doc->RetrieveDataAttribute('foo'));
			$doc->ApplyValueForDataAttribute('foo', null);
			static::assertNull($doc->RetrieveDataAttribute('foo'));

			$doc = $this->AbstractHtmlElementFromCtorArgs($class, $ctorargs);
		}

		if ($decorateDocument instanceof Closure) {
			$decorateDocument($doc, $this);
		}

		static::assertSame(
			$expected,
			$doc->MarkupContentToDocumentString($content)
		);
	}

	/**
	* @param class-string<AbstractHtmlElement> $class
	* @param class-string<Throwable> $expectedExceptionClass
	* @param array<int, scalar|array{!element:string}>|null $content
	*
	* @dataProvider dataProviderBadDocumentToString
	*
	* @depends testIsAbstractHtmlElement
	*/
	public function testBadDocumentToString(
		string $class,
		array $ctorargs,
		string $expectedExceptionClass,
		string $expectedExceptionMessage,
		array $content = null,
		Closure $decorateDocument = null
	) : void {
		$doc = $this->AbstractHtmlElementFromCtorArgs($class, $ctorargs);

		if ($decorateDocument instanceof Closure) {
			$decorateDocument($doc);
		}

		$this->expectException($expectedExceptionClass);
		$this->expectExceptionMessage($expectedExceptionMessage);

		$doc->MarkupContentToDocumentString($content);
	}

	/**
	* @param class-string<AbstractHtmlElement> $class
	* @param string[] $firstSet
	* @param string[] $assertSameFirstSetExpected
	* @param string[] $append
	* @param string[] $assertSameAppendExpected
	* @param string[] $assertSameSortExpected
	* @param string[] $assertSameSortSetExpected
	*
	* @dataProvider dataProviderStringArrayMethods
	*
	* @depends testIsAbstractHtmlElement
	*/
	public function testStringArrayMethods(
		string $class,
		array $ctorargs,
		string $methodSuffix,
		array $firstSet,
		array $assertSameFirstSetExpected,
		array $append,
		array $assertSameAppendExpected,
		array $assertSameSortExpected,
		array $assertSameSortSetExpected
	) : void {
		$doc = $this->AbstractHtmlElementFromCtorArgs($class, $ctorargs);

		$getMethod = 'Get' . $methodSuffix;
		$setMethod = 'Set' . $methodSuffix;
		$appendMethod = 'Append' . $methodSuffix;
		$clearMethod = 'Clear' . $methodSuffix;

		static::assertTrue(method_exists($doc, $getMethod));
		static::assertTrue(method_exists($doc, $setMethod));
		static::assertTrue(method_exists($doc, $appendMethod));
		static::assertTrue(method_exists($doc, $clearMethod));

		static::assertEmpty($doc->$getMethod());

		$doc->$setMethod(...$firstSet);
		static::assertSame($assertSameFirstSetExpected, $doc->$getMethod());

		$doc->$appendMethod(...$append);
		static::assertSame($assertSameAppendExpected, $doc->$getMethod());

		/**
		* @var string[]
		*/
		$val = $doc->$getMethod();
		sort($val);
		static::assertSame($assertSameSortExpected, $doc->$getMethod());
		$doc->$setMethod(...$val);
		static::assertSame($assertSameSortSetExpected, $doc->$getMethod());

		$doc->$clearMethod();
		static::assertEmpty($doc->$getMethod());
	}

	/**
	* @param class-string<AbstractHtmlElement> $class
	* @param scalar|null $expected
	*
	* @dataProvider dataProviderTestDefaults
	*
	* @depends testIsAbstractHtmlElement
	*/
	public function testTranslateDefault(string $class, string $methodSuffix, $expected) : void
	{
		$doc = $this->AbstractHtmlElementFromCtorArgs($class);

		$method = 'Get' . $methodSuffix;

		if ( ! method_exists($doc, $method)) {
			static::markTestSkipped(sprintf('%s does not implement method "%s"', $class, $method));
		} else {
			static::assertSame($expected, $doc->$method());
		}
	}

	/**
	* @param class-string<Document> $class
	*
	* @dataProvider dataProviderDocumentInstance
	*/
	public function testDocumentDefaults(
		string $class,
		array $ctorargs
	) : void {
		/**
		* @var Document
		*/
		$doc = $this->AbstractHtmlElementFromCtorArgs($class, $ctorargs);

		static::assertSame([], $doc->GetAccessKey());
		$doc->SetAccessKey('a');
		static::assertSame(['a'], $doc->GetAccessKey());
		$doc->AppendAccessKey('b');
		static::assertSame(['a', 'b'], $doc->GetAccessKey());
		$doc->ClearAccessKey();
		static::assertSame([], $doc->GetAccessKey());

		static::assertNull($doc->GetAutoCapitalize());
		$doc->SetAutoCapitalize('words');
		static::assertSame('words', $doc->GetAutoCapitalize());

		static::assertSame([], $doc->GetClass());
		$doc->SetClass('foo');
		static::assertSame(['foo'], $doc->GetClass());
		$doc->AppendClass('bar');
		static::assertSame(['foo', 'bar'], $doc->GetClass());
		$doc->ClearClass();
		static::assertSame([], $doc->GetClass());

		static::assertFalse($doc->GetContentEditable());
		$doc->SetContentEditable(true);
		static::assertTrue($doc->GetContentEditable());
		$doc->SetContentEditable(false);
		static::assertFalse($doc->GetContentEditable());

		static::assertNull($doc->GetContextMenu());
		$doc->SetContextMenu('foo');
		static::assertSame('foo', $doc->GetContextMenu());
		$doc->SetContextMenu(null);
		static::assertNull($doc->GetContextMenu());

		static::assertNull($doc->GetDir());
		$doc->SetDir('ltr');
		static::assertSame('ltr', $doc->GetDir());
		$doc->SetDir(null);
		static::assertNull($doc->GetDir());

		static::assertFalse($doc->GetDraggable());
		$doc->SetDraggable(true);
		static::assertTrue($doc->GetDraggable());
		$doc->SetDraggable(false);
		static::assertFalse($doc->GetDraggable());
		$doc->SetDraggable(null);
		static::assertFalse($doc->GetDraggable());

		static::assertNull($doc->GetDropzone());
		$doc->SetDropzone('copy');
		static::assertSame('copy', $doc->GetDropzone());
		$doc->SetDropzone(null);
		static::assertNull($doc->GetDropzone());

		static::assertFalse($doc->GetHidden());
		$doc->SetHidden(true);
		static::assertTrue($doc->GetHidden());
		$doc->SetHidden(false);
		static::assertFalse($doc->GetHidden());

		static::assertNull($doc->GetId());
		$doc->SetId('foo');
		static::assertSame('foo', $doc->GetId());
		$doc->SetId(null);
		static::assertNull($doc->GetId());

		static::assertNull($doc->GetIs());
		$doc->SetIs('foo');
		static::assertSame('foo', $doc->GetIs());
		$doc->SetIs(null);
		static::assertNull($doc->GetIs());

		static::assertNull($doc->GetLang());
		$doc->SetLang('foo');
		static::assertSame('foo', $doc->GetLang());
		$doc->SetLang(null);
		static::assertNull($doc->GetLang());

		static::assertNull($doc->GetSlot());
		$doc->SetSlot('foo');
		static::assertSame('foo', $doc->GetSlot());
		$doc->SetSlot(null);
		static::assertNull($doc->GetSlot());

		static::assertFalse($doc->GetSpellcheck());
		$doc->SetSpellcheck(true);
		static::assertTrue($doc->GetSpellcheck());
		$doc->SetSpellcheck(false);
		static::assertFalse($doc->GetSpellcheck());

		static::assertSame([], $doc->GetStyle());
		$doc->SetStyle('foo');
		static::assertSame(['foo'], $doc->GetStyle());
		$doc->AppendStyle('bar');
		static::assertSame(['foo', 'bar'], $doc->GetStyle());
		$doc->ClearStyle();
		static::assertSame([], $doc->GetStyle());

		static::assertNull($doc->GetTitleAttribute());
		$doc->SetTitleAttribute('foo');
		static::assertSame('foo', $doc->GetTitleAttribute());
		$doc->SetTitleAttribute(null);
		static::assertNull($doc->GetTitleAttribute());

		static::assertTrue($doc->GetTranslate());
		$doc->SetTranslate(false);
		static::assertFalse($doc->GetTranslate());
		$doc->SetTranslate(true);
		static::assertTrue($doc->GetTranslate());

		static::assertNull($doc->GetItemId());
		$doc->SetItemId('foo');
		static::assertSame('foo', $doc->GetItemId());
		$doc->SetItemId(null);
		static::assertNull($doc->GetItemId());

		static::assertSame([], $doc->GetItemRefs());
		$doc->SetItemRefs('foo');
		static::assertSame(['foo'], $doc->GetItemRefs());
		$doc->AppendItemRefs('bar');
		static::assertSame(['foo', 'bar'], $doc->GetItemRefs());
		$doc->ClearItemRefs();
		static::assertSame([], $doc->GetItemRefs());

		static::assertFalse($doc->GetItemScope());
		$doc->SetItemScope(true);
		static::assertTrue($doc->GetItemScope());
		$doc->SetItemScope(false);
		static::assertFalse($doc->GetItemScope());

		static::assertNull($doc->GetItemType());
		$doc->SetItemType('foo');
		static::assertSame('foo', $doc->GetItemType());
		$doc->SetItemType(null);
		static::assertNull($doc->GetItemType());
	}

	/**
	* @param class-string<AbstractHtmlElement> $class
	*/
	protected function AbstractHtmlElementFromCtorArgs(
		string $class,
		array $ctorargs = []
	) : AbstractHtmlElement {
		/**
		* @var AbstractHtmlElement
		*/
		$doc = 0 === count($ctorargs) ? new $class() : new $class(...$ctorargs);

		return $doc;
	}
}
