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
        ];
    }
    
    public function removeAccent($str): string
    {
        return StringUtils::stripAccents($str);
    }
}

