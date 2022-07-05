<?php

namespace App\Exception;

class FileTooLargeUploadException extends \Exception
{
	
    private $uploaded;
    private $max;
	
    public function __construct(int $uploaded, int $max)
	{
	    $this->uploaded = $uploaded;
	    $this->max = $max;
	}
	
	public function getMax()
	{
	    return $this->max;
	}
	
	public function getUploaded()
	{
	    return $this->uploaded;
	}
	
}