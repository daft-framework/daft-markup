<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup;

trait MarkupConverterTrait
{
	protected ? Markup $markup = null;

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
