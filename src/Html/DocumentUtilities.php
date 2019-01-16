<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup\Html;

class DocumentUtilities
{
    const BOOL_IN_ARRAY_STRICT = true;

    const INT_ARRAY_FILL_START_AT_ZERO = 0;

    /**
    * @return array<int, string>
    */
    public static function ExcludeUrls(array $existing, string ...$urls) : array
    {
        /**
        * @var array<int, string>
        */
        $out = array_filter($existing, function (string $url) use ($urls) : bool {
            return ! in_array($url, $urls, self::BOOL_IN_ARRAY_STRICT);
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
        $fresh = array_combine(
            $urls,
            array_fill(
                self::INT_ARRAY_FILL_START_AT_ZERO,
                count($urls),
                $setting
            )
        );

        return array_merge($existing, $fresh);
    }
}
