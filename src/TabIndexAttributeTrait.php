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

    /**
    * @return int|null
    */
    public function GetTabIndex()
    {
        return $this->tabindex;
    }

    public function SetTabIndex(int $value = null)
    {
        $this->tabindex = $value;
    }
}
