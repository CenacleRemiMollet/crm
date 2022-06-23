<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use App\Util\StringUtils;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('remove_accent', [$this, 'removeAccent']),
            new TwigFilter('integer', [$this, 'toInteger']),
        ];
    }
    
    public function removeAccent($str): string
    {
        return StringUtils::stripAccents($str);
    }
    
    public function toInteger(string $str): int
    {
        return intval($str);
    }
    
}

