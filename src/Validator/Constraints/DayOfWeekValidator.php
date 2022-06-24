<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class DayOfWeekValidator extends ConstraintValidator
{
	public function validate($value, Constraint $constraint)
	{
	    if (!$constraint instanceof DayOfWeek) {
	        throw new UnexpectedTypeException($constraint, DayOfWeek::class);
		}
		if (null === $value || '' === $value) {
			return;
		}
		if (!is_string($value)) {
			throw new UnexpectedValueException($value, 'string');
		}
		$lower = strtolower($value);
		if($lower !== 'monday'
		    && $lower !== 'tuesday'
		    && $lower !== 'wednesday'
		    && $lower !== 'thursday'
		    && $lower !== 'friday'
		    && $lower !== 'saturday'
		    && $lower !== 'sunday') {
		    $this->context->buildViolation($constraint->unvalidValueMessage)
		        ->setParameter('{{ string }}', $value)
		        ->addViolation();
		}
	}

}

