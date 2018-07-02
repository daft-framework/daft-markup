<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup;

trait HtmlMicrodataAttributeTrait
{
    use HtmlAttributeAbstractsTrait;

    public function GetItemId() : ? string
    {
        return $this->RetrieveNullableStringAttribute('itemid');
    }

    public function SetItemId(? string $value) : void
    {
        $this->ApplyValueForNullableStringAttribute('itemid', $value);
    }

    public function GetItemRefs() : array
    {
        return $this->RetrieveStringArrayAttributeValues('itemref');
    }

    public function SetItemRefs(string ...$values) : void
    {
        $this->ApplyValueForStringArrayAttribute('itemref', ...$values);
    }

    public function ClearItemRefs() : void
    {
        $this->ClearValueForStringArrayAttribute('itemref');
    }

    public function AppendItemRefs(string ...$appendThese) : void
    {
        $this->AppendValueForStringArrayAttribute('itemref', ...$appendThese);
    }

    public function GetItemScope() : bool
    {
        return $this->RetrieveBooleanAttributeValue('itemscope');
    }

    public function SetItemScope(bool $value) : void
    {
        $this->ApplyBooleanAttributeValue('itemscope', $value);
    }

    public function GetItemType() : ? string
    {
        return $this->RetrieveNullableStringAttribute('itemtype');
    }

    public function SetItemType(? string $value) : void
    {
        $this->ApplyValueForNullableStringAttribute('itemtype', $value);
    }
}
