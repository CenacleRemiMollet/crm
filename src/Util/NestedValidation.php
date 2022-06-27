<?php
namespace App\Util;

use Symfony\Component\Validator\ConstraintViolationListInterface;

interface NestedValidation
{
    public function validateNested(RequestUtil $requestUtil): ConstraintViolationListInterface;
}

