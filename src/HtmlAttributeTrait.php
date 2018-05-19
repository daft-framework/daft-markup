<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup;

trait HtmlAttributeTrait
{
    use TabIndexAttributeTrait;

    public function GetAccessKey() : array
    {
        return $this->RetrieveStringArrayAttributeValues('accesskey');
    }

    public function ClearAccessKey() : void
    {
        $this->ClearValueForStringArrayAttribute('accesskey');
    }

    public function SetAccessKey(string ...$parts) : void
    {
        $this->ApplyValueForStringArrayAttribute('accesskey', ...$parts);
    }

    public function AppendAccessKey(string ...$parts) : void
    {
        $this->AppendValueForStringArrayAttribute('accesskey', ...$parts);
    }

    public function GetAutoCapitalize() : ? string
    {
        return $this->RetrieveNullableStringAttribute('autocapitalize');
    }

    public function SetAutoCapitalize(? string $value) : void
    {
        $this->ApplyValueForNullableStringAttribute('autocapitalize', $value);
    }

    public function GetClass() : array
    {
        return $this->RetrieveStringArrayAttributeValues('class');
    }

    public function ClearClass() : void
    {
        $this->ClearValueForStringArrayAttribute('class');
    }

    public function SetClass(string ...$parts) : void
    {
        $this->ApplyValueForStringArrayAttribute('class', ...$parts);
    }

    public function AppendClass(string ...$parts) : void
    {
        $this->AppendValueForStringArrayAttribute('class', ...$parts);
    }

    public function GetContentEditable() : bool
    {
        return $this->RetrieveBooleanAttributeValue('contenteditable');
    }

    public function SetContentEditable(bool $value) : void
    {
        $this->ApplyBooleanAttributeValue('contenteditable', $value);
    }

    public function GetContextMenu() : ? string
    {
        return $this->RetrieveNullableStringAttribute('contextmenu');
    }

    public function SetContextMenu(? string $value) : void
    {
        $this->ApplyValueForNullableStringAttribute('contextmenu', $value);
    }

    public function GetDir() : ? string
    {
        return $this->RetrieveNullableStringAttribute('dir');
    }

    public function SetDir(? string $value) : void
    {
        $this->ApplyValueForNullableStringAttribute('dir', $value);
    }

    public function GetDraggable() : bool
    {
        return $this->RetrieveBooleanAttributeValue('draggable');
    }

    public function SetDraggable(? bool $value) : void
    {
        $this->ApplyBooleanAttributeValue('draggable', $value);
    }

    public function GetDropzone() : ? string
    {
        return $this->RetrieveNullableStringAttribute('dropzone');
    }

    public function SetDropzone(? string $value) : void
    {
        $this->ApplyValueForNullableStringAttribute('dropzone', $value);
    }

    public function GetHidden() : bool
    {
        return $this->RetrieveBooleanAttributeValue('hidden');
    }

    public function SetHidden(bool $value) : void
    {
        $this->ApplyBooleanAttributeValue('hidden', $value);
    }

    public function GetId() : ? string
    {
        return $this->RetrieveNullableStringAttribute('id');
    }

    public function SetId(? string $value) : void
    {
        $this->ApplyValueForNullableStringAttribute('id', $value);
    }

    public function GetIs() : ? string
    {
        return $this->RetrieveNullableStringAttribute('is');
    }

    public function SetIs(? string $value) : void
    {
        $this->ApplyValueForNullableStringAttribute('is', $value);
    }

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

    public function GetLang() : ? string
    {
        return $this->RetrieveNullableStringAttribute('lang');
    }

    public function SetLang(? string $value) : void
    {
        $this->ApplyValueForNullableStringAttribute('lang', $value);
    }

    public function GetSlot() : ? string
    {
        return $this->RetrieveNullableStringAttribute('slot');
    }

    public function SetSlot(? string $value) : void
    {
        $this->ApplyValueForNullableStringAttribute('slot', $value);
    }

    public function GetSpellcheck() : bool
    {
        return $this->RetrieveBooleanAttributeValue('spellcheck');
    }

    public function SetSpellcheck(? bool $value) : void
    {
        $this->ApplyBooleanAttributeValue('spellcheck', $value);
    }

    public function GetStyle() : array
    {
        return $this->RetrieveStringArrayAttributeValues('style');
    }

    public function ClearStyle() : void
    {
        $this->ClearValueForStringArrayAttribute('style');
    }

    public function SetStyle(string ...$parts) : void
    {
        $this->ApplyValueForStringArrayAttribute('style', ...$parts);
    }

    public function AppendStyle(string ...$parts) : void
    {
        $this->AppendValueForStringArrayAttribute('style', ...$parts);
    }

    public function GetTitleAttribute() : ? string
    {
        return $this->RetrieveNullableStringAttribute('title');
    }

    public function SetTitleAttribute(? string $value) : void
    {
        $this->ApplyValueForNullableStringAttribute('title', $value);
    }

    public function GetTranslate() : bool
    {
        return $this->RetrieveBooleanAttributeValue('translate');
    }

    public function SetTranslate(bool $value) : void
    {
        $this->ApplyBooleanAttributeValue('translate', $value);
    }

    abstract protected function RetrieveStringArrayAttributeValues(string $attribute) : array;

    abstract protected function ClearValueForStringArrayAttribute(string $attribute) : void;

    abstract protected function ApplyValueForStringArrayAttribute(
        string $attribute,
        string ...$values
    ) : void;

    abstract protected function AppendValueForStringArrayAttribute(
        string $attribute,
        string ...$values
    ) : void;

    abstract protected function RetrieveNullableStringAttribute(string $attribute) : ? string;

    abstract protected function ApplyValueForNullableStringAttribute(
        string $attribute,
        ? string $value
    ) : void;

    abstract protected function RetrieveBooleanAttributeValue(string $attribute) : bool;

    abstract protected function ApplyBooleanAttributeValue(
        string $attribute,
        ? bool $value
    ) : void;
}
