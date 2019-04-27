<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup;

/**
* @template TElement as array{!element:string, !attributes?:array<string, scalar|array<int, scalar>>, !content?:array<int, scalar|TElement>}
*/
abstract class AbstractHtmlElement
{
    use TabIndexAttributeTrait;

    const ENUMERATED_BOOLEANS = [
        'draggable',
        'spellcheck',
    ];

    const BOOL_IN_ARRAY_STRICT = true;

    /**
    * @var array<string, string>
    */
    protected $nullableStringAttributes = [];

    /**
    * @var array<string, array<int, string>>
    */
    protected $stringArrayAttributes = [];

    /**
    * @var array<string, bool|null>
    */
    protected $nullableBooleanAttributes = [
        'translate' => true,
    ];

    /**
    * @param array<int|string, mixed>|null $markup
    *
    * @psalm-param array<int, scalar|TElement>|null $markup
    */
    abstract public function MarkupContentToDocumentString(array $markup = null) : string;

    abstract public static function MarkupElementName() : string;

    /**
    * @param array<int|string, mixed>|null $content
    *
    * @psalm-param array<int, scalar|TElement>|null $content
    *
    * @return array<int|string, mixed>
    *
    * @psalm-return TElement
    */
    public function ToMarkupArray(array $content = null) : array
    {
        $out = [
            '!element' => static::MarkupElementName(),
            '!attributes' => $this->MarkupAttributes(),
        ];

        if (is_array($content)) {
            $out['!content'] = $content;
        }

        return $out;
    }

    public function RetrieveDataAttribute(string $attr) : ? string
    {
        return $this->RetrieveNullableStringAttribute('data-' . $attr);
    }

    public function ApplyValueForDataAttribute(string $attribute, ? string $value) : void
    {
        $this->ApplyValueForNullableStringAttribute('data-' . $attribute, $value);
    }

    /**
    * @return array<string, scalar|array<int, scalar>>
    */
    public function MarkupAttributes() : array
    {
        /**
        * @var array<string, scalar|bool|array<int, scalar>>
        */
        $out = [];

        /**
        * @var array<int, array<string, bool|scalar|array<int, scalar>>>
        */
        $groupedAttributes = $this->GroupedAttributes();

        foreach ($groupedAttributes as $group) {
            foreach ($group as $attribute => $value) {
                $out[$attribute] = $value;
            }
        }

        return self::MarkupAttributesPostProcess($out);
    }

    /**
    * @param array<string, scalar|bool|array<int, scalar>> $out
    *
    * @return array<string, scalar|array<int, scalar>>
    */
    protected static function MarkupAttributesPostProcess(array $out) : array
    {
        foreach ($out as $attribute => $value) {
            if (
                in_array($attribute, self::ENUMERATED_BOOLEANS, self::BOOL_IN_ARRAY_STRICT) &&
                is_bool($value)
            ) {
                $out[$attribute] = $value ? 'true' : 'false';
            }
        }

        ksort($out);

        if (false === ($out['translate'] ?? null)) {
            $out['translate'] = 'no';
        } else {
            unset($out['translate']);
        }

        return $out;
    }

    /**
    * @var array<int, array<string, bool|scalar|array<int, scalar>>>
    */
    protected function GroupedAttributes() : array
    {
        /**
        * @var array<int, array<string, bool|scalar|array<int, scalar>>>
        */
        $groupedAttributes = array_map(
            function (array $group) : array {
                return array_filter(
                    $group,
                    /**
                    * @param mixed $value
                    */
                    function ($value) : bool {
                        return ! is_null($value);
                    }
                );
            },
            [
                $this->nullableStringAttributes,
                $this->stringArrayAttributes,
                $this->nullableBooleanAttributes,
                [
                    'tabindex' => $this->GetTabIndex(),
                ],
            ]
        );

        return $groupedAttributes;
    }

    protected function RetrieveNullableStringAttribute(string $attribute) : ? string
    {
        return $this->nullableStringAttributes[$attribute] ?? null;
    }

    protected function ApplyValueForNullableStringAttribute(
        string $attribute,
        ? string $value
    ) : void {
        if (is_string($value)) {
            $this->nullableStringAttributes[$attribute] = $value;
        } else {
            unset($this->nullableStringAttributes[$attribute]);
        }
    }

    protected function RetrieveStringArrayAttributeValues(string $attribute) : array
    {
        return $this->stringArrayAttributes[$attribute] ?? [];
    }

    protected function ClearValueForStringArrayAttribute(string $attribute) : void
    {
        unset($this->stringArrayAttributes[$attribute]);
    }

    protected function ApplyValueForStringArrayAttribute(
        string $attribute,
        string ...$values
    ) : void {
        $this->stringArrayAttributes[$attribute] = $values;
    }

    protected function AppendValueForStringArrayAttribute(
        string $attribute,
        string ...$values
    ) : void {
        $this->stringArrayAttributes[$attribute] = array_merge(
            $this->stringArrayAttributes[$attribute] ?? [],
            $values
        );
    }

    protected function RetrieveBooleanAttributeValue(string $attribute) : bool
    {
        return $this->nullableBooleanAttributes[$attribute] ?? false;
    }

    protected function ApplyBooleanAttributeValue(string $attribute, ? bool $value) : void
    {
        if (is_null($value)) {
            unset($this->nullableBooleanAttributes[$attribute]);
        } else {
            $this->nullableBooleanAttributes[$attribute] = $value;
        }
    }
}
