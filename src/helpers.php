<?php

if (! function_exists('to_carbon')) {

    /**
     * 转换为 Carbon
     *
     * @param string|int|\Illuminate\Support\Carbon      $datetime
     * @param string|int|\Illuminate\Support\Carbon|null $parseFormat
     *
     * @return \Illuminate\Support\Carbon|int|string
     */
    function to_carbon($datetime, $parseFormat = null)
    {
        if ($datetime instanceof \Illuminate\Support\Carbon) {
            return $datetime;
        }

        if (is_string($datetime) || is_numeric($datetime)) {
            switch (strlen($datetime)) {
                case 6:
                    $datetime = \Illuminate\Support\Carbon::createFromFormat($parseFormat ?? 'Ym', $datetime);
                    break;

                case 7:
                    $datetime = \Illuminate\Support\Carbon::createFromFormat($parseFormat ?? 'Y-m', $datetime);
                    break;
            }
        }

        return $datetime instanceof \DateTime ? $datetime : \Illuminate\Support\Carbon::parse($datetime);
    }
}

if (!function_exists('object_to_array')) {
    /**
     * PHP 对象转数组.
     *
     * @param $obj
     *
     * @return array
     */
    function object_to_array($obj)
    {
        if (empty($obj)) {
            return (array) $obj;
        }

        return json_decode(json_encode($obj), true);
    }
}
