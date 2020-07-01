<?php

namespace App\Helpers;

class Format
{
    public static function toHMS($seconds)
    {
        $h = floor($seconds / 3600);
        $seconds = $seconds % 3600;
        $m = floor($seconds / 60);
        $seconds = $seconds % 60;
        $output = "";
        if ($h < 10) $output .= "0";
        $output .= $h . ":";
        if ($m < 10) $output .= "0";
        $output .= $m . ":";
        if ($seconds < 10) $output .= "0";
        $output .= $seconds;
        return $output;
    }

    public static function percent($n, $d, $precision = 1)
    {
        if ($d == 0) return "0%";
        return round($n / $d * 100, $precision) . " % ";
    }

    public static function average($n, $d, $precision = 0)
    {
        $avg = 0;
        if ($d > 0) $avg = $n / $d;
        return round($avg, $precision);
    }
}