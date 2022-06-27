<?php

namespace App\Util;

use App\Exception\ViolationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;
use Exception;

class RequestUtil
{
	private $serializer;
	private $validator;
	private $violator;

	public function __construct(SerializerInterface $serializer, TranslatorInterface $translator)
	{
		$this->serializer = $serializer;
		$this->validator = Validation::createValidatorBuilder()
		      ->enableAnnotationMapping()
		      ->getValidator();
		$this->violator = new ViolationUtil($translator);
	}

	public function validate(Request $request, string $model)
	{
	    $data = $request->getContent();
		if ( ! $data) {
			throw new BadRequestHttpException('Empty body.');
		}
		try {
		    $object = $this->serializer->deserialize($data, $model, 'json');
		} catch (Exception $e) {
		    throw new BadRequestHttpException('Invalid body, '.$e->getMessage().' - '.$data);
		}
		
		return $this->validateObject($object, $model);
	}

	public function validateObject($object, string $model)
	{
	    if(is_array($object)) {
	        foreach($object as &$obj) {
	            $this->valid($obj);
	        }
	    } else {
	        $this->validOne($object);
	    }
	    
	    return $object;
	}
	
	public function findErrors($object)
	{
	    $errors = $this->validator->validate($object);
	    if(is_array($object) || $object instanceof \Traversable) {
	        foreach($object as &$o) {
	            $this->nested($o, $errors);
	        }
	    } else {
	        $this->nested($object, $errors);
	    }
	    return $errors;
	}
	
	//*************************************************
	
	private function valid($object)
	{
		if(is_array($object)) {
			foreach($object as &$obj) {
				$this->valid($obj);
			}
		} else {
			$this->validOne($object);
		}
	}

	private function validOne($object)
	{
	    $errors = $this->findErrors($object);
		if ($errors->count()) {
			throw new ViolationException($this->violator->build($errors));
		}
	}

	private function nested($object, $errors)
	{
	    if($object instanceof NestedValidation)
	    {
	        $nestedErrors = $object->validateNested($this);
	        if( ! empty($nestedErrors)) {
	            foreach ($nestedErrors as &$nestedError) {
	                $errors->add($nestedError);
	            }
	        }
	    }
	}
	
}