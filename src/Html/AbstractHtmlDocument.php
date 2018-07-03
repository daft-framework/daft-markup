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

    /**
    * @param null|array<int|string, mixed> $content
    */
    public function MarkupContentToDocumentString(array $content = null) : string
    {
        return
            static::MarkupContentDoctype() .
            "\n" .
            $this->GetMarkupConverter()->MarkupArrayToMarkupString($this->ToMarkupArray($content));
    }

    public static function MarkupElementName() : string
    {
        return 'html';
    }

    protected static function MarkupContentDoctype() : string
    {
        return '<!DOCTYPE html>';
    }
}
