<?php
namespace App\Controller;


class ControllerUtils
{
    public static function parseDisciplines($str)
    {
        return ControllerUtils::parseCommaSeparated($str, ['taekwondo', 'taekwonkido', 'hapkido', 'sinkido', 'gumdo']);
    }
    
    public static function parseDays($str)
    {
        return ControllerUtils::parseCommaSeparated($str, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
    }
    
    private static function parseCommaSeparated($str, $expecteds) {
        $out = array();
        foreach (array_map('trim', explode(',', $str)) as &$d) {
            foreach ($expecteds as &$expected) {
                if(strcasecmp($d, $expected) == 0) {
                    array_push($out, $expected);
                    break;
                }
            }
        }
        return array_unique($out);
    }
}

