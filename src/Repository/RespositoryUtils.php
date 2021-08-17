<?php
namespace App\Repository;

class RespositoryUtils
{

	public function __construct()
	{
		$em->getClassMetadata('YourBundle:Product')->getFieldNames();
	}
}

