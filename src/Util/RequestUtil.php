<?php

namespace App\Util;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Exception\ViolationException;

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

	public function validate(Request $request, string $model): object
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

		$errors = $this->validator->validate($object);

		if ($errors->count()) {
			throw new ViolationException($this->violator->build($errors));
		}

		return $object;
	}
}