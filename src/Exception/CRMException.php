<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class CRMException extends HttpException
{
	private $details;
    
    public function __construct(int $statusCode, $message, ?array $details = null)
	{
	    parent::__construct($statusCode, $message);
	    $this->details = $details;
	}
	
	public function getDetails()
	{
	    return $this->details;
	}
	
	
}