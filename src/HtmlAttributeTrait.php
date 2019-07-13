<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup;

/**
* @psalm-type AUTOCAPITALIZE = 'off'|'none'|'on'|'sentences'|'words'|'characters'
* @psalm-type DIR = 'ltr'|'rtl'|'auto'
* @psalm-type DROPZONE = 'copy'|'move'|'link'
*/
trait HtmlAttributeTrait
{
	use HtmlAttributeAbstractsTrait;
	use HtmlMicrodataAttributeTrait;
	use TabIndexAttributeTrait;

	/**
	* @return array<int, string>
	*/
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

	/**
	* @return AUTOCAPITALIZE|null
	*/
	public function GetAutoCapitalize() : ? string
	{
		/**
		* @var AUTOCAPITALIZE|null
		*/
		$out = $this->RetrieveNullableStringAttribute('autocapitalize');

		return $out;
	}

	/**
	* @param AUTOCAPITALIZE|null $value
	*/
	public function SetAutoCapitalize(? string $value) : void
	{
		$this->ApplyValueForNullableStringAttribute('autocapitalize', $value);
	}

	/**
	* @return array<int, string>
	*/
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

	/**
	* @return DIR|null
	*/
	public function GetDir() : ? string
	{
		/**
		* @var DIR|null
		*/
		$out = $this->RetrieveNullableStringAttribute('dir');

		return $out;
	}

	/**
	* @param DIR|null $value
	*/
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

	/**
	* @return DROPZONE|null
	*/
	public function GetDropzone() : ? string
	{
		/**
		* @var DROPZONE|null
		*/
		$out = $this->RetrieveNullableStringAttribute('dropzone');

		return $out;
	}

	/**
	* @param DROPZONE|null $value
	*/
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
}
