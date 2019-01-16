<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup;

trait HtmlMicrodataAttributeTrait
{
    use HtmlAttributeAbstractsTrait;

    /**
    * @return string|null
    */
    public function GetItemId()
    {
        return $this->RetrieveNullableStringAttribute('itemid');
    }

    public function SetItemId(string $value = null)
    {
        $this->ApplyValueForNullableStringAttribute('itemid', $value);
    }

    public function GetItemRefs() : array
    {
        return $this->RetrieveStringArrayAttributeValues('itemref');
    }

    public function SetItemRefs(string ...$values)
    {
        $this->ApplyValueForStringArrayAttribute('itemref', ...$values);
    }

    public function ClearItemRefs()
    {
        $this->ClearValueForStringArrayAttribute('itemref');
    }

    public function AppendItemRefs(string ...$appendThese)
    {
        $this->AppendValueForStringArrayAttribute('itemref', ...$appendThese);
    }

    public function GetItemScope() : bool
    {
        return $this->RetrieveBooleanAttributeValue('itemscope');
    }

    public function SetItemScope(bool $value)
    {
        $this->ApplyBooleanAttributeValue('itemscope', $value);
    }

    /**
    * @return string|null
    */
    public function GetItemType()
    {
        return $this->RetrieveNullableStringAttribute('itemtype');
    }

    public function SetItemType(string $value = null)
    {
        $this->ApplyValueForNullableStringAttribute('itemtype', $value);
    }
}
