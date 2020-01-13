<?php


namespace Naveed\Utils\Helpers;


/**
 * Class DB
 * @package Naveed\Utils\Helpers
 */
class DB
{
    /**
     * Returns the current database driver
     * @return string
     */
    public static function driver()
    {
        $connection = config('database.default');
        return config("database.connections.{$connection}.driver");
    }
}
