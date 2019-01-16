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

abstract class AbstractHtmlDocument extends AbstractHtmlElement
{
    use HtmlAttributeTrait;
    use MarkupConverterTrait;

    const COUNT_NON_EMPTY = 0;

    /**
    * @var string|null
    */
    protected $title = null;

    /**
    * @var array<string, string>
    */
    protected $preloads = [];

    /**
    * @var array<int, string>
    */
    protected $stylesheets = [];

    /**
    * @var array<int, string>
    */
    protected $scripts = [];

    /**
    * @var array<int, string>
    */
    protected $async = [];

    /**
    * @var array<int, string>
    */
    protected $defer = [];

    /**
    * @var array<int, string>
    */
    protected $modules = [];

    /**
    * @var array<int, string>
    */
    protected $noModules = [];

    /**
    * @var array<int, array<string, string>>
    */
    protected $metas = [];

    /**
    * @var string
    */
    protected $charset = 'utf-8';

    /**
    * @var array<string, string>
    */
    protected $crossOrigin = [];

    /**
    * @var array<string, string>
    */
    protected $integrity = [];

    /**
    * @var bool
    */
    protected $enableIntegrityOnPreload = false;

    /**
    * @param array<int|string, mixed>|null $content
    *
    * @return array<int|string, mixed>
    */
    public function ToMarkupArray(array $content = null) : array
    {
        $bodyContent = array_merge(
            self::CoalesceToArray($content),
            array_map([$this, 'ScriptsToMarkupArrayMapper'], $this->scripts)
        );

        $content = [['!element' => 'head', '!content' => $this->HeadContentMarkupArray()]];

        if (count($bodyContent) > self::COUNT_NON_EMPTY) {
            $content[] = ['!element' => 'body', '!content' => $bodyContent];
        }

        return parent::ToMarkupArray($content);
    }

    public function Preload(string $as, string ...$urls)
    {
        $this->preloads = DocumentUtilities::MergeSetting($this->preloads, $as, ...$urls);
    }

    public function IncludeCss(string ...$urls)
    {
        $this->stylesheets = array_unique(array_merge($this->stylesheets, $urls));
    }

    public function ExcludeCss(string ...$urls)
    {
        $this->stylesheets = DocumentUtilities::ExcludeUrls($this->stylesheets, ...$urls);
    }

    public function GetCharset() : string
    {
        return $this->charset;
    }

    public function SetCharset(string $charset)
    {
        $this->charset = $charset;
    }

    public function includeJs(string ...$urls)
    {
        $this->scripts = array_unique(array_merge($this->scripts, $urls));
    }

    public function deferJs(string ...$urls)
    {
        $this->includeJs(...$urls);
        /**
        * @var array<int, string>
        */
        $urls = array_unique(array_merge($this->defer, $urls));

        $this->defer = $urls;
    }

    public function asyncJs(string ...$urls)
    {
        $this->includeJs(...$urls);
        /**
        * @var array<int, string>
        */
        $urls = array_unique(array_merge($this->async, $urls));

        $this->async = $urls;
    }

    public function ExcludeJs(string ...$urls)
    {
        $this->scripts = DocumentUtilities::ExcludeUrls($this->scripts, ...$urls);
    }

    public function IncludeModules(string ...$urls)
    {
        $this->includeJs(...$urls);
        /**
        * @var array<int, string>
        */
        $urls = array_unique(array_merge($this->modules, $urls));

        $this->modules = $urls;
    }

    public function IncludeNoModules(string ...$urls)
    {
        $this->includeJs(...$urls);
        /**
        * @var array<int, string>
        */
        $urls = array_unique(array_merge($this->noModules, $urls));

        $this->noModules = $urls;
    }

    public function CrossOrigin(string $setting, string ...$urls)
    {
        $this->crossOrigin = DocumentUtilities::MergeSetting(
            $this->crossOrigin,
            $setting,
            ...$urls
        );
    }

    public function ConfigureIntegrity(string $url, string $integrity)
    {
        $this->integrity[$url] = $integrity;
    }

    public function AppendMeta(string $name, string $content)
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

    public function SetEnableIntegrityOnPreload(bool $value)
    {
        $this->enableIntegrityOnPreload = $value;
    }

    /**
    * @param array<int|string, mixed>|null $content
    */
    public function MarkupContentToDocumentString(array $content = null) : string
    {
        return
            static::MarkupContentDoctype() .
            "\n" .
            $this->GetMarkupConverter()->MarkupArrayToMarkupString($this->ToMarkupArray($content));
    }

    public function GetTitle() : string
    {
        return trim((string) ($this->title ?? null));
    }

    public function SetTitle(string $value)
    {
        $value = trim($value);

        $this->title = '' !== $value ? $value : null;
    }

    public static function MarkupElementName() : string
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
                function (array $meta) : array {
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
        $attrs['async'] = in_array($url, $this->async, true);
        $attrs['defer'] = in_array($url, $this->defer, true);
        if (in_array($url, $this->modules, true)) {
            $attrs['type'] = 'module';
        } elseif (in_array($url, $this->noModules, true)) {
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

    final protected static function CoalesceToArray(array $content = null) : array
    {
        return $content ?? [];
    }
}
