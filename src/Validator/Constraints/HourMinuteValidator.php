<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class HourMinuteValidator extends ConstraintValidator
{
	public function validate($value, Constraint $constraint)
	{
	    if (!$constraint instanceof HourMinute) {
		    throw new UnexpectedTypeException($constraint, HourMinute::class);
		}
		if (null === $value || '' === $value) {
			return;
		}
		if (!is_string($value)) {
			throw new UnexpectedValueException($value, 'string');
		}
		if (preg_match("/^([0-9]{1,2})\:([0-9]{1,2})$/", $value, $matches)) {
		    if (! $this->checkTime($matches[1], $matches[2])) {
				$this->context->buildViolation($constraint->unvalidValueMessage)
					->setParameter('{{ string }}', $value)
					->addViolation();
		    }
		} else {
			$this->context->buildViolation($constraint->validFormatMessage)
    			->setParameter('{{ string }}', $value)
    			->addViolation();
		}
	}

	private function checkTime(string $hours, string $minutes):bool
	{
	    $h = intval($hours);
	    if($h < 0 || $h > 23) {
	        return false;
	    }
	    $m = intval($minutes);
	    return $m >= 0 && $m <= 59;
	}
}

