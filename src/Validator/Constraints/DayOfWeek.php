<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DayOfWeek extends Constraint
{
	public $unvalidValueMessage = 'Must be a day of week in english: {{ string }}';
}

