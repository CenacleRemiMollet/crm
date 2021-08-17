<?php

namespace App\Util;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ViolationUtil
{
    private $translator;
    
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    
    
    public function build(ConstraintViolationListInterface $violations): array
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] =  $this->translator->trans($violation->getMessage());
        }
        
        return $this->buildMessages($errors);
    }
    
    private function buildMessages(array $errors): array
    {
        $result = [];
        
        foreach ($errors as $path => $message) {
            $temp = &$result;
            
            foreach (explode('.', $path) as $key) {
                preg_match('/(.*)(\[.*?\])/', $key, $matches);
                if ($matches) {
                    $index = str_replace(['[', ']'], '', $matches[2]);
                    $temp = &$temp[$this->translator->trans($matches[1])][$index];
                } else {
                    $temp = &$temp[$key];
                }
            }
            
            $temp = $message;
        }
        
        return $result;
    }
}