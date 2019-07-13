<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup;

trait MarkupConverterTrait
{
	/**
	* @var Markup|null
	*/
	protected $markup;

	public function GetMarkupConverter() : Markup
	{
		if ( ! ($this->markup instanceof Markup)) {
			$this->markup = new Markup();
		}

		return $this->markup;
	}

	public function SetMarkupConverter(Markup $converter) : void
	{
		$this->markup = $converter;
	}
}
