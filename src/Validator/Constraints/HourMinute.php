<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class HourMinute extends Constraint
{
	public $validFormatMessage = 'The time should like HH:mm: {{ string }}';
	public $unvalidValueMessage = 'The time is not valid: {{ string }}';
}

