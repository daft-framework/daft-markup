<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup;

trait HtmlAttributeAbstractsTrait
{
    abstract protected function RetrieveStringArrayAttributeValues(string $attribute) : array;

    abstract protected function ClearValueForStringArrayAttribute(string $attribute);

    abstract protected function ApplyValueForStringArrayAttribute(
        string $attribute,
        string ...$values
    );

    abstract protected function AppendValueForStringArrayAttribute(
        string $attribute,
        string ...$values
    );

    /**
    * @return string|null
    */
    abstract protected function RetrieveNullableStringAttribute(string $attribute);

    abstract protected function ApplyValueForNullableStringAttribute(
        string $attribute,
        string $value = null
    );

    abstract protected function RetrieveBooleanAttributeValue(string $attribute) : bool;

    abstract protected function ApplyBooleanAttributeValue(
        string $attribute,
        bool $value = null
    );
}
