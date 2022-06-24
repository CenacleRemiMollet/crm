<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class NoHTMLValidator extends ConstraintValidator
{
	public function validate($value, Constraint $constraint)
	{
	    if (!$constraint instanceof NoHTML) {
	        throw new UnexpectedTypeException($constraint, NoHTML::class);
		}
		if (null === $value || '' === $value) {
			return;
		}
		if (!is_string($value)) {
			throw new UnexpectedValueException($value, 'string');
		}
		if ($value != strip_tags($value)) {
			$this->context->buildViolation($constraint->unvalidValueMessage)
				->addViolation();
		}
	}

}

