<?php

namespace App\Exception;

class UnauthorizedTypeUploadException extends \Exception
{
	
	private $type;
	
	public function __construct($type)
	{
	    $this->type = $type;
	}
	
	public function getType()
	{
	    return $this->type;
	}
	
}