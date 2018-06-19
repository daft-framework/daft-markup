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

class Document extends AbstractHtmlElement
{
    use HtmlAttributeTrait;
    use MarkupConverterTrait;

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

    public static function MarkupElementName() : string
    {
        return 'html';
    }

    /**
    * @param null|array<int|string, mixed> $content
    */
    public function MarkupContentToDocumentString(array $content = null) : string
    {
        return
            '<!DOCTYPE html>' .
            "\n" .
            $this->GetMarkupConverter()->MarkupArrayToMarkupString($this->ToMarkupArray($content));
    }

    /**
    * @param null|array<int|string, mixed> $content
    *
    * @return array<int|string, mixed>
    */
    public function ToMarkupArray(array $content = null) : array
    {
        $bodyContent = array_merge(($content ?? []), $this->ScriptsToMarkupArray());

        $content = [['!element' => 'head', '!content' => $this->HeadContentMarkupArray()]];

        if (count($bodyContent) > 0) {
            $content[] = ['!element' => 'body', '!content' => $bodyContent];
        }

        return parent::ToMarkupArray($content);
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

    public function Preload(string $as, string ...$urls) : void
    {
        /**
        * @var array<string, string> $pl
        */
        $pl = array_merge($this->preloads, array_combine($urls, array_fill(0, count($urls), $as)));

        $this->preloads = $pl;
    }

    public function IncludeCss(string ...$urls) : void
    {
        $this->stylesheets = array_unique(array_merge($this->stylesheets, $urls));
    }

    public function ExcludeCss(string ...$urls) : void
    {
        $this->stylesheets = array_filter(
            $this->stylesheets,
            function (string $url) use ($urls) : bool {
                return ! in_array($url, $urls, true);
            }
        );
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
        $this->scripts = array_unique(array_merge($this->scripts, $urls));
    }

    public function deferJs(string ...$urls) : void
    {
        $this->includeJs(...$urls);
        /**
        * @var array<int, string> $urls
        */
        $urls = array_unique(array_merge($this->defer, $urls));

        $this->defer = $urls;
    }

    public function asyncJs(string ...$urls) : void
    {
        $this->includeJs(...$urls);
        /**
        * @var array<int, string> $urls
        */
        $urls = array_unique(array_merge($this->async, $urls));

        $this->async = $urls;
    }

    public function ExcludeJs(string ...$urls) : void
    {
        $this->scripts = array_filter($this->scripts, function (string $url) use ($urls) : bool {
            return ! in_array($url, $urls, true);
        });
    }

    public function IncludeModules(string ...$urls) : void
    {
        $this->includeJs(...$urls);
        /**
        * @var array<int, string> $urls
        */
        $urls = array_unique(array_merge($this->modules, $urls));

        $this->modules = $urls;
    }

    public function IncludeNoModules(string ...$urls) : void
    {
        $this->includeJs(...$urls);
        /**
        * @var array<int, string> $urls
        */
        $urls = array_unique(array_merge($this->noModules, $urls));

        $this->noModules = $urls;
    }

    public function CrossOrigin(string $setting, string ...$urls) : void
    {
        foreach ($urls as $url) {
            $this->crossOrigin[$url] = $setting;
        }
    }

    public function ConfigureIntegrity(string $url, string $integrity) : void
    {
        $this->integrity[$url] = $integrity;
    }

    public function AppendMeta(string $name, string $content) : void
    {
        if (preg_match('/^http:(.+)$/', $name, $matches)) {
            $this->metas[] = [
                'http-equiv' => (string) $matches[1],
                'content' => $content,
            ];
        } else {
            $this->metas[] = [
                'name' => $name,
                'content' => $content,
            ];
        }
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
    * @return string[]
    */
    public function GetPossibleHeaders() : array
    {
        return array_map([$this, 'GetPossibleHeadersMapper'], array_keys($this->preloads));
    }

    public function ClearPossibleHeaderSources() : void
    {
        $this->preloads = [];
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
            $this->PreloadsToMarkupArray(),
            $this->StylesheetsToMarkupArray(),
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
        $attrs = $this->MaybeDecorateAttrs(
            [
                'rel' => 'preload',
                'href' => $url,
                'as' => $this->preloads[$url],
            ],
            $url,
            true
        );

        if ('module' === $attrs['as']) {
            $attrs['rel'] = 'modulepreload';
            unset($attrs['as']);
        }

        return ['!element' => 'link', '!attributes' => $attrs];
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

        return $attrs;
    }

    protected function MaybeDecorateAttrs(
        array $attrs,
        string $url,
        bool $checkPreload = false,
        bool $checkAsScript = false
    ) : array {
        $checkIntegrity = ( ! $checkPreload || $this->GetEnableIntegrityOnPreload());
        if ($checkAsScript) {
            $attrs = $this->MaybeDecorateScriptAttrs($attrs, $url);
        }
        if (isset($this->crossOrigin[$url])) {
            $attrs['crossorigin'] = $this->crossOrigin[$url];
        }
        if (isset($this->integrity[$url]) && $checkIntegrity) {
            $attrs['integrity'] = $this->integrity[$url];
        }

        return $attrs;
    }

    protected function PreloadsToMarkupArray() : array
    {
        return array_map([$this, 'PreloadsToMarkupArrayMapper'], array_keys($this->preloads));
    }

    protected function StylesheetsToMarkupArrayMapper(string $url) : array
    {
        return [
            '!element' => 'link',
            '!attributes' => $this->MaybeDecorateAttrs(
                [
                'rel' => 'stylesheet',
                'href' => $url,
                ],
                $url
            ),
        ];
    }

    protected function StylesheetsToMarkupArray() : array
    {
        return array_map([$this, 'StylesheetsToMarkupArrayMapper'], $this->stylesheets);
    }

    protected function ScriptsToMarkupArrayMapper(string $url) : array
    {
        return [
            '!element' => 'script',
            '!attributes' => $this->MaybeDecorateAttrs(
                [],
                $url,
                false,
                true
            ),
        ];
    }

    protected function ScriptsToMarkupArray() : array
    {
        return array_map([$this, 'ScriptsToMarkupArrayMapper'], $this->scripts);
    }

    protected function GetPossibleHeadersMapper(string $url) : string
    {
        $as = $this->preloads[$url];

        $out = sprintf(
            'Link: <%s>; rel=%s; as=%s',
            $url,
            (('module' !== $as) ? 'preload' : 'modulepreload'),
            $as
        );

        if ($this->GetEnableIntegrityOnPreload() && isset($this->integrity[$url])) {
            $out .= '; integrity=' . $this->integrity[$url];
        }

        return $out;
    }
}
