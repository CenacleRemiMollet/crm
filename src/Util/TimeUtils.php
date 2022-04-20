<?php
namespace App\Util;

class TimeUtils
{
    public static function parseTime($strtime): \DateTime
    {
        return \DateTime::createFromFormat('H:i', $strtime);
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

