<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup\Html;

class DocumentUtilities
{
    /**
    * @return array<int, string>
    */
    public static function ExcludeUrls(array $existing, string ...$urls) : array
    {
        /**
        * @var array<int, string>
        */
        $out = array_filter($existing, function (string $url) use ($urls) : bool {
            return ! in_array($url, $urls, true);
        });

        return $out;
    }

    /**
    * @param array<string, string> $existing
    *
    * @return array<string, string>
    */
    public static function MergeSetting(array $existing, string $setting, string ...$urls) : array
    {
        /**
        * @var array<string, string>
        */
        $fresh = array_combine($urls, array_fill(0, count($urls), $setting));

        return array_merge($existing, $fresh);
    }
}
