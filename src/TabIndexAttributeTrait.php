<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMarkup;

trait TabIndexAttributeTrait
{
	/**
	* @var int|null
	*/
	protected $tabindex = null;

	public function GetTabIndex() : ? int
	{
		return $this->tabindex;
	}

	public function SetTabIndex(? int $value) : void
	{
		$this->tabindex = $value;
	}
}
