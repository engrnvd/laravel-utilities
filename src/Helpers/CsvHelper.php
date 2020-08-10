<?php

namespace App\Helpers;

class CsvHelper
{
    public static function create($filepath, $data)
    {
        $fp = fopen($filepath, 'w');

        foreach ($data as $row) {
            fputcsv($fp, $row);
        }

        fclose($fp);
    }

    public static function get($filepath)
    {
        $result = [];
        if (($handle = fopen($filepath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $result[] = $data;
            }
            fclose($handle);
        }
        return $result;
    }
}
