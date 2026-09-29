<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Birthday extends Constraint
{
	public $validFormatMessage = 'The birthday date should like dd/mm/YYYY: {{ string }}';
	public $unvalidValueMessage = 'The birthday date is not valid: {{ string }}';
	public $tooYoungMessage = 'Too young, min date: {{ string }}';
	public $tooOldMessage = 'Too old, max date: {{ string }}';
}

