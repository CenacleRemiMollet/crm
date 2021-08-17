<?php
namespace App\Util;

use Doctrine\Common\Persistence\Proxy;

class DiffTool
{

	public function __construct()
	{
		
	}
	
	public static function toArray($object)
	{
		$ref = new \ReflectionClass(get_class($object));
		if($object instanceof Proxy) {
			$ref = $ref->getParentClass();
		}
		$array = [];
		foreach ($ref->getProperties() as $prop) {
			$prop->setAccessible(true);
			$value = $prop->getValue($object);
			if($value instanceof \DateTime) {
				$value = $value->format(\DateTime::RFC3339_EXTENDED);
			} else if(is_object($value)) {
				$value = json_encode($value, true);
			}
			$array[$prop->getName()] = $value;
		}
		return $array;
	}
}

