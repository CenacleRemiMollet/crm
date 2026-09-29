<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class HourMinute extends Constraint
{
	public $validFormatMessage = 'The time should like HH:mm: {{ string }}';
	public $unvalidValueMessage = 'The time is not valid: {{ string }}';
}

