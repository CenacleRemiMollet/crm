<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class DayOfWeek extends Constraint
{
	public $unvalidValueMessage = 'Must be a day of week in english: {{ string }}';
}

