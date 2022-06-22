<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class CRMException extends HttpException
{
	
    public function __construct(int $statusCode, $message)
	{
	    parent::__construct($statusCode, $message);
	}
	
}