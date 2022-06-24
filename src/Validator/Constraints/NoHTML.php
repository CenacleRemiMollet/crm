<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NoHTML extends Constraint
{
	public $unvalidValueMessage = 'HTML tags are forbidden';
}

