<?php
namespace App\Util;

class DateIntervalUtils
{
	public static function toIso8601(\DateInterval $interval) {
		list($date, $time) = explode("T",$interval->format("P%yY%mM%dDT%hH%iM%sS"));
		// now, we need to remove anything that is a zero, but make sure to not remove something like 10D or 20D
		$res = str_replace([ 'M0D', 'Y0M', 'P0Y' ], [ 'M', 'Y', 'P' ], $date)
			   .rtrim(str_replace([ 'M0S', 'H0M', 'T0H'], [ 'M', 'H', 'T' ], "T$time"),"T");
		if($res == 'P') { // edge case - if we remove everything, DateInterval will hate us later
			return 'PT0S';
		}
		return $res;
	}
	
	public static function parseHourDoubleDotsMinute($strtime): \DateInterval
	{
	    return new \DateInterval('PT'.str_replace(':', 'H', $strtime).'M');
	}

	public static function getTotalMinutes(\DateInterval $interval){
	    return ($interval->d * 24 * 60) + ($interval->h * 60) + $interval->i;
	}
	
	public static function getTotalHours(\DateInterval $interval){
	    return ($interval->d * 24) + $interval->h + $interval->i / 60;
	}
}

