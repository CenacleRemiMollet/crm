<?php
namespace App\Security;

use App\Exception\ViolationException;
use App\Util\ViolationUtil;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;

class PasswordValidator
{

	private $validator;
	private $violator;
	
	public function __construct(TranslatorInterface $translator)
	{
		$this->validator = Validation::createValidatorBuilder()
			->enableAnnotationMapping()
			->getValidator();
		$this->violator = new ViolationUtil($translator);
	}
	
	public function validate(string $password)
	{
		$errors = $this->validator->validate(new PasswordObject($password));
		
		if ($errors->count()) {
			throw new ViolationException($this->violator->build($errors));
		}
		
	}
}

