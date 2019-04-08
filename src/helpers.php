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

        return $datetime instanceof \Illuminate\Support\Carbon ? $datetime : \Illuminate\Support\Carbon::parse($datetime);
    }
}
