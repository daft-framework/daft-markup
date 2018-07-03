<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup\Html;

use BadMethodCallException;

class Document extends AbstractHtmlDocument
{
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
