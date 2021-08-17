<?php
namespace App\Util;

class DateUtils
{
    public static function parseFrenchToDateTime($date)
    {
        if (preg_match("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/", $date, $matches)) {
            return new \DateTime($matches[2].'/'.$matches[1].'/'.$matches[3]);
        }
        return null;
    }
    
    public static function toFrench($date)
    {
        if($date instanceof \DateTime) {
            return $date->format("d/m/Y");
        }
        return null;
    }
    
    public static function toArray(\DateTime $date)
    {
        return [
                 'iso8601' => $date->format(\DateTime::ISO8601),
                 'date' => $date->format("Y-m-d"),
                 'time' => $date->format("H:i:s"),
                 'date_fr' => $date->format("d/m/Y"),
               ];
    }
    
}

