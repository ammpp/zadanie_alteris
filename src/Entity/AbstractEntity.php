<?php
namespace App\Entity;

abstract class AbstractEntity
{
	public function normalize()
	{
		return get_object_vars($this);
	}
}
