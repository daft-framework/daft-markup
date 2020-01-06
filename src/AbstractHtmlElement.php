<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup;

/**
* @template T1 as string
* @template T2 as array<string, scalar|list<scalar>>
* @template T3 as list<scalar|array{!element:string}>
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
	protected array $nullableStringAttributes = [];

	/**
	* @var array<string, list<string>>
	*/
	protected array $stringArrayAttributes = [];

	/**
	* @var array<string, bool|null>
	*/
	protected $nullableBooleanAttributes = [
		'translate' => true,
	];

	public function __construct()
	{
	}

	/**
	* @param T3|null $markup
	*/
	abstract public function MarkupContentToDocumentString(array $markup = null) : string;

	/**
	* @return T1
	*/
	abstract public function MarkupElementName() : string;

	/**
	* @param T3|null $content
	*
	* @return array{!element:T1, !attributes:T2, !content?:T3}
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

		/**
		* @var array{!element:T1, !attributes:T2, !content?:T3}
		*/
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
	* @return T2
	*/
	public function MarkupAttributes() : array
	{
		/**
		* @var T2
		*/
		$out = [];

		/**
		* @var list<T2>
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
	* @var list<T2>
	*/
	protected function GroupedAttributes() : array
	{
		/**
		* @var list<T2>
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

	/**
	* @return list<string>
	*/
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

	/**
	* @param T2 $out
	*
	* @return T2
	*/
	private function MarkupAttributesPostProcess(array $out) : array
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

		/**
		* @var T2
		*/
		$out = $out;

		return $out;
	}
}
