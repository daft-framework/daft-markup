<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup\Html;

/**
* @template T1 as array<string, scalar|list<scalar>>
* @template T2 as list<scalar|array{!element:string}>
*
* @template-extends AbstractHtmlDocument<T1, T2>
*/
class Document extends AbstractHtmlDocument
{
	/**
	* @return list<string>
	*/
	public function GetPossibleHeaders() : array
	{
		return array_map([$this, 'GetPossibleHeadersMapper'], array_keys($this->preloads));
	}

	public function ClearPossibleHeaderSources() : void
	{
		$this->preloads = [];
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
