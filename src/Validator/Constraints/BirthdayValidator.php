<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class BirthdayValidator extends ConstraintValidator
{
	public function validate($value, Constraint $constraint)
	{
		if (!$constraint instanceof Birthday) {
			throw new UnexpectedTypeException($constraint, Birthday::class);
		}
		if (null === $value || '' === $value) {
			return;
		}
		if (!is_string($value)) {
			throw new UnexpectedValueException($value, 'string');
		}
		if (preg_match("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/", $value, $matches)) {
			if (! checkdate($matches[2], $matches[1], $matches[3])) {
				$this->context->buildViolation($constraint->unvalidValueMessage)
					->setParameter('{{ string }}', $value)
					->addViolation();
			}
			$maxDate = new \DateTime("now");
			$maxDate->sub(new \DateInterval('P2Y'));
			$minDate = new \DateTime("now");
			$minDate->sub(new \DateInterval('P100Y'));
			$dateLocalizedEN = $matches[2].'/'.$matches[1].'/'.$matches[3];
			$date = new \DateTime($dateLocalizedEN);
			if($maxDate < $date) {
				$this->context->buildViolation($constraint->tooYoungMessage)
				->setParameter('{{ string }}', $maxDate->format('d/m/Y'))
					->addViolation();
			}
			if($minDate > $date) {
				$this->context->buildViolation($constraint->tooOldMessage)
				->setParameter('{{ string }}', $minDate->format('d/m/Y'))
				->addViolation();
			}
		} else {
			$this->context->buildViolation($constraint->validFormatMessage)
			->setParameter('{{ string }}', $value)
				->addViolation();
		}
	}

}

