<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup\Html;

use BadMethodCallException;
use SignpostMarv\DaftMarkup\AbstractHtmlElement;
use SignpostMarv\DaftMarkup\HtmlAttributeTrait;
use SignpostMarv\DaftMarkup\MarkupConverterTrait;

/**
 * @template T1 as array<string, scalar|list<scalar>>
 * @template T2 as list<scalar|array{!element:string}>
 *
 * @template-extends AbstractHtmlElement<'html', T1, T2>
 */
abstract class AbstractHtmlDocument extends AbstractHtmlElement
{
	use HtmlAttributeTrait;
	use MarkupConverterTrait;

	const COUNT_NON_EMPTY = 0;

	protected ? string $title = null;

	/**
	 * @var array<string, string>
	 */
	protected array $preloads = [];

	/**
	 * @var list<string>
	 */
	protected array $stylesheets = [];

	/**
	 * @var list<string>
	 */
	protected array $scripts = [];

	/**
	 * @var list<string>
	 */
	protected array $async = [];

	/**
	 * @var list<string>
	 */
	protected array $defer = [];

	/**
	 * @var list<string>
	 */
	protected array $modules = [];

	/**
	 * @var list<string>
	 */
	protected array $noModules = [];

	/**
	 * @var list<array<string, string>>
	 */
	protected array $metas = [];

	protected string $charset = 'utf-8';

	/**
	 * @var array<string, string>
	 */
	protected array $crossOrigin = [];

	/**
	 * @var array<string, string>
	 */
	protected array $integrity = [];

	protected bool $enableIntegrityOnPreload = false;

	/**
	 * @param T2|null $content
	 *
	 * @return array{!element:'html', !attributes:T1, !content:T2}
	 */
	public function ToMarkupArray(array $content = null) : array
	{
		$bodyContent = array_merge(
			self::CoalesceToArray($content),
			array_map([$this, 'ScriptsToMarkupArrayMapper'], $this->scripts)
		);

		/**
		 * @var T2
		 */
		$content = [['!element' => 'head', '!content' => $this->HeadContentMarkupArray()]];

		if (count($bodyContent) > self::COUNT_NON_EMPTY) {
			$content[] = ['!element' => 'body', '!content' => $bodyContent];
		}

		/**
		 * @var T2
		 */
		$content = $content;

		/**
		 * @var array{!element:'html', !attributes:T1, !content:T2}
		 */
		return parent::ToMarkupArray($content);
	}

	public function Preload(string $as, string ...$urls) : void
	{
		$this->preloads = DocumentUtilities::MergeSetting($this->preloads, $as, ...$urls);
	}

	public function IncludeCss(string ...$urls) : void
	{
		$this->stylesheets = array_values(array_unique(array_merge($this->stylesheets, $urls)));
	}

	public function ExcludeCss(string ...$urls) : void
	{
		$this->stylesheets = DocumentUtilities::ExcludeUrls($this->stylesheets, ...$urls);
	}

	public function GetCharset() : string
	{
		return $this->charset;
	}

	public function SetCharset(string $charset) : void
	{
		$this->charset = $charset;
	}

	public function includeJs(string ...$urls) : void
	{
		$this->scripts = array_values(array_unique(array_merge($this->scripts, $urls)));
	}

	public function deferJs(string ...$urls) : void
	{
		$this->includeJs(...$urls);

		$this->defer = array_values(array_unique(array_merge($this->defer, $urls)));
	}

	public function asyncJs(string ...$urls) : void
	{
		$this->includeJs(...$urls);

		$this->async = array_values(array_unique(array_merge($this->async, $urls)));
	}

	public function ExcludeJs(string ...$urls) : void
	{
		$this->scripts = DocumentUtilities::ExcludeUrls($this->scripts, ...$urls);
	}

	public function IncludeModules(string ...$urls) : void
	{
		$this->includeJs(...$urls);

		$this->modules = array_values(array_unique(array_merge($this->modules, $urls)));
	}

	public function IncludeNoModules(string ...$urls) : void
	{
		$this->includeJs(...$urls);

		$this->noModules = array_values(array_unique(array_merge($this->noModules, $urls)));
	}

	public function CrossOrigin(string $setting, string ...$urls) : void
	{
		$this->crossOrigin = DocumentUtilities::MergeSetting(
			$this->crossOrigin,
			$setting,
			...$urls
		);
	}

	public function ConfigureIntegrity(string $url, string $integrity) : void
	{
		$this->integrity[$url] = $integrity;
	}

	public function AppendMeta(string $name, string $content) : void
	{
		$key = 'name';
		$val = $name;

		if (preg_match('/^http:(.+)$/', $name, $matches) > 0) {
			$key = 'http-equiv';
			$val = $matches[1];
		}

		$this->metas[] = [
			$key => $val,
			'content' => $content,
		];
	}

	public function GetEnableIntegrityOnPreload() : bool
	{
		return $this->enableIntegrityOnPreload;
	}

	public function SetEnableIntegrityOnPreload(bool $value) : void
	{
		$this->enableIntegrityOnPreload = $value;
	}

	/**
	 * @param T2|null $content
	 */
	public function MarkupContentToDocumentString(array $content = null) : string
	{
		/**
		 * @var array{
		 *	!element:string,
		 *	!attributes:array<string, scalar|list<scalar>>,
		 *	!content?:list<scalar|array{!element:string}>
		 * }
		 */
		$to_convert = $this->ToMarkupArray($content);

		return
			static::MarkupContentDoctype() .
			"\n" .
			$this->GetMarkupConverter()->MarkupArrayToMarkupString($to_convert);
	}

	public function GetTitle() : string
	{
		return trim((string) ($this->title ?? null));
	}

	public function SetTitle(string $value) : void
	{
		$value = trim($value);

		$this->title = '' !== $value ? $value : null;
	}

	public function MarkupElementName() : string
	{
		return 'html';
	}

	protected function HeadContentMarkupArray() : array
	{
		$title = $this->GetTitle();

		if ('' === $title) {
			throw new BadMethodCallException('Document title must not be empty!');
		}

		return array_merge(
			[
				['!element' => 'meta', '!attributes' => ['charset' => $this->GetCharset()]],
				['!element' => 'title', '!content' => [$title]],
			],
			array_map([$this, 'PreloadsToMarkupArrayMapper'], array_keys($this->preloads)),
			array_map([$this, 'StylesheetsToMarkupArrayMapper'], $this->stylesheets),
			array_map(
				static function (array $meta) : array {
					return ['!element' => 'meta', '!attributes' => $meta];
				},
				$this->metas
			)
		);
	}

	protected function PreloadsToMarkupArrayMapper(string $url) : array
	{
		return ['!element' => 'link', '!attributes' => $this->MaybeDecoratePreloadAttrs($url)];
	}

	protected function MaybeDecoratePreloadAttrs(string $url) : array
	{
		$attrs = ['rel' => 'preload', 'href' => $url, 'as' => $this->preloads[$url]];

		if ('module' === $attrs['as']) {
			$attrs['rel'] = 'modulepreload';
			unset($attrs['as']);
		}

		return $this->MaybeDecorateAttrs($attrs, $url, $this->GetEnableIntegrityOnPreload());
	}

	protected function MaybeDecorateScriptAttrs(array $attrs, string $url) : array
	{
		$attrs['src'] = $url;
		$attrs['async'] = in_array($url, $this->async, self::BOOL_IN_ARRAY_STRICT);
		$attrs['defer'] = in_array($url, $this->defer, self::BOOL_IN_ARRAY_STRICT);
		if (in_array($url, $this->modules, self::BOOL_IN_ARRAY_STRICT)) {
			$attrs['type'] = 'module';
		} elseif (in_array($url, $this->noModules, self::BOOL_IN_ARRAY_STRICT)) {
			$attrs['nomodule'] = true;
		}

		return $this->MaybeDecorateAttrs($attrs, $url);
	}

	protected function MaybeDecorateAttrs(
		array $attrs,
		string $url,
		bool $checkIntegrity = true
	) : array {
		if (isset($this->crossOrigin[$url])) {
			$attrs['crossorigin'] = $this->crossOrigin[$url];
		}
		if ($checkIntegrity && isset($this->integrity[$url])) {
			$attrs['integrity'] = $this->integrity[$url];
		}

		return $attrs;
	}

	protected function MaybeDecorateAttrsStylesheet(string $url) : array
	{
		return $this->MaybeDecorateAttrs(['rel' => 'stylesheet', 'href' => $url], $url);
	}

	protected function StylesheetsToMarkupArrayMapper(string $url) : array
	{
		return ['!element' => 'link', '!attributes' => $this->MaybeDecorateAttrsStylesheet($url)];
	}

	protected function ScriptsToMarkupArrayMapper(string $url) : array
	{
		return [
			'!element' => 'script',
			'!attributes' => $this->MaybeDecorateScriptAttrs([], $url),
		];
	}

	protected static function MarkupContentDoctype() : string
	{
		return '<!DOCTYPE html>';
	}

	final protected static function CoalesceToArray(? array $content) : array
	{
		return $content ?? [];
	}
}
