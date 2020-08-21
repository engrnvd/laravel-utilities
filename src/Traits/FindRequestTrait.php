<?php

namespace Naveed\Utils\Traits;

use Carbon\Carbon;
use Illuminate\Support\Arr;

trait FindRequestTrait
{
    /**
     * @param $query \Illuminate\Database\Eloquent\Builder|null
     * @return array
     */
    public static function findRequested($query = null)
    {
        if (!$query) $query = self::query();
        if(request('trashed')) $query->onlyTrashed();
        // search results based on user input
        foreach (self::$findKeys as $key => $param) {
            if (!\Request::has($key)) continue;
            $type = is_string($param) ? $param : Arr::get($param, 'type', 'string');
            $operator = is_array($param) ? Arr::get($param, 'op') : null;
            $value = request($key);
            if (!in_array($type, ['bool', 'boolean']) && !$value) continue;
            switch ($type) {
                case 'bool':
                case 'boolean':
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'date':
                    $range = json_decode($value, true); //Request string value may contain dates
                    if (json_last_error() !== 0) {
                        $range = [
                            'start' => $value,
                            'end' => $value,
                        ];
                    }
                    $value = [
                        Carbon::parse($range['start'])->startOfDay(),
                        Carbon::parse($range['end'])->endOfDay()
                    ];
                    $operator = 'between';
                    break;
                case 'int':
                case 'integer':
                    $value = (int)$value;
                    break;
                case 'string':
                    if (!$operator) $operator = 'like';
                    break;
            }
            if (!$operator) $operator = "=";
            switch ($operator) {
                case "between":
                    $query->whereBetween($key, $value);
                    break;
                case "like":
                    $value = "%{$value}%";
                //skip the break and let it fall in default case
                default:
                    $query->where($key, $operator, $value);
            }

        }
        // sort results
        if (\Request::has("sort")) $query->orderBy(request("sort"), request("sortType", "asc"));

        // paginate results
        if ($resPerPage = request("perPage"))
            return $query->paginate(intval($resPerPage));
        return $query->get();
    }
}