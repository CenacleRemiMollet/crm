<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class RolesValidator extends ConstraintValidator
{
	public function validate($value, Constraint $constraint)
	{
	    if (!$constraint instanceof Roles) {
	        throw new UnexpectedTypeException($constraint, Roles::class);
		}
		if (null === $value) {
			return;
		}
		if (!is_array($value)) {
			throw new UnexpectedValueException($value, 'array');
		}
		$forbiddenValues = array();
		foreach ($value as &$r) {
		    if( ! in_array(strtoupper($r), \App\Security\Roles::ROLES)) {
		        $forbiddenValues[] = $r;
		    }
		}
		if( ! empty($forbiddenValues)) {
            $this->context->buildViolation($constraint->unvalidValueMessage)
                ->setParameter('{{ string }}', implode(', ', $forbiddenValues))
			    ->addViolation();
		}
	}

}

