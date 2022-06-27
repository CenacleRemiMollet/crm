<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Roles extends Constraint
{
	public $unvalidValueMessage = 'Unknown roles: {{ string }}';
}

