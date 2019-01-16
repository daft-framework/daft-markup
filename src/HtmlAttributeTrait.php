<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup;

trait HtmlAttributeTrait
{
    use HtmlAttributeAbstractsTrait;
    use HtmlMicrodataAttributeTrait;
    use TabIndexAttributeTrait;

    public function GetAccessKey() : array
    {
        return $this->RetrieveStringArrayAttributeValues('accesskey');
    }

    public function ClearAccessKey()
    {
        $this->ClearValueForStringArrayAttribute('accesskey');
    }

    public function SetAccessKey(string ...$parts)
    {
        $this->ApplyValueForStringArrayAttribute('accesskey', ...$parts);
    }

    public function AppendAccessKey(string ...$parts)
    {
        $this->AppendValueForStringArrayAttribute('accesskey', ...$parts);
    }

    /**
    * @return string|null
    */
    public function GetAutoCapitalize()
    {
        return $this->RetrieveNullableStringAttribute('autocapitalize');
    }

    public function SetAutoCapitalize(string $value = null)
    {
        $this->ApplyValueForNullableStringAttribute('autocapitalize', $value);
    }

    public function GetClass() : array
    {
        return $this->RetrieveStringArrayAttributeValues('class');
    }

    public function ClearClass()
    {
        $this->ClearValueForStringArrayAttribute('class');
    }

    public function SetClass(string ...$parts)
    {
        $this->ApplyValueForStringArrayAttribute('class', ...$parts);
    }

    public function AppendClass(string ...$parts)
    {
        $this->AppendValueForStringArrayAttribute('class', ...$parts);
    }

    public function GetContentEditable() : bool
    {
        return $this->RetrieveBooleanAttributeValue('contenteditable');
    }

    public function SetContentEditable(bool $value)
    {
        $this->ApplyBooleanAttributeValue('contenteditable', $value);
    }

    /**
    * @return string|null
    */
    public function GetContextMenu()
    {
        return $this->RetrieveNullableStringAttribute('contextmenu');
    }

    public function SetContextMenu(string $value = null)
    {
        $this->ApplyValueForNullableStringAttribute('contextmenu', $value);
    }

    /**
    * @return string|null
    */
    public function GetDir()
    {
        return $this->RetrieveNullableStringAttribute('dir');
    }

    public function SetDir(string $value = null)
    {
        $this->ApplyValueForNullableStringAttribute('dir', $value);
    }

    public function GetDraggable() : bool
    {
        return $this->RetrieveBooleanAttributeValue('draggable');
    }

    public function SetDraggable(bool $value = null)
    {
        $this->ApplyBooleanAttributeValue('draggable', $value);
    }

    /**
    * @return string|null
    */
    public function GetDropzone()
    {
        return $this->RetrieveNullableStringAttribute('dropzone');
    }

    public function SetDropzone(string $value = null)
    {
        $this->ApplyValueForNullableStringAttribute('dropzone', $value);
    }

    public function GetHidden() : bool
    {
        return $this->RetrieveBooleanAttributeValue('hidden');
    }

    public function SetHidden(bool $value)
    {
        $this->ApplyBooleanAttributeValue('hidden', $value);
    }

    /**
    * @return string|null
    */
    public function GetId()
    {
        return $this->RetrieveNullableStringAttribute('id');
    }

    public function SetId(string $value = null)
    {
        $this->ApplyValueForNullableStringAttribute('id', $value);
    }

    /**
    * @return string|null
    */
    public function GetIs()
    {
        return $this->RetrieveNullableStringAttribute('is');
    }

    public function SetIs(string $value = null)
    {
        $this->ApplyValueForNullableStringAttribute('is', $value);
    }

    /**
    * @return string|null
    */
    public function GetLang()
    {
        return $this->RetrieveNullableStringAttribute('lang');
    }

    public function SetLang(string $value = null)
    {
        $this->ApplyValueForNullableStringAttribute('lang', $value);
    }

    /**
    * @return string|null
    */
    public function GetSlot()
    {
        return $this->RetrieveNullableStringAttribute('slot');
    }

    public function SetSlot(string $value = null)
    {
        $this->ApplyValueForNullableStringAttribute('slot', $value);
    }

    public function GetSpellcheck() : bool
    {
        return $this->RetrieveBooleanAttributeValue('spellcheck');
    }

    public function SetSpellcheck(bool $value = null)
    {
        $this->ApplyBooleanAttributeValue('spellcheck', $value);
    }

    public function GetStyle() : array
    {
        return $this->RetrieveStringArrayAttributeValues('style');
    }

    public function ClearStyle()
    {
        $this->ClearValueForStringArrayAttribute('style');
    }

    public function SetStyle(string ...$parts)
    {
        $this->ApplyValueForStringArrayAttribute('style', ...$parts);
    }

    public function AppendStyle(string ...$parts)
    {
        $this->AppendValueForStringArrayAttribute('style', ...$parts);
    }

    /**
    * @return string|null
    */
    public function GetTitleAttribute()
    {
        return $this->RetrieveNullableStringAttribute('title');
    }

    public function SetTitleAttribute(string $value = null)
    {
        $this->ApplyValueForNullableStringAttribute('title', $value);
    }

    public function GetTranslate() : bool
    {
        return $this->RetrieveBooleanAttributeValue('translate');
    }

    public function SetTranslate(bool $value)
    {
        $this->ApplyBooleanAttributeValue('translate', $value);
    }
}
