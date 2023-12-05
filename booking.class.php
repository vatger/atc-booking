<?php

class Booking
{
    var string $name;
    var string $abbreviation;
    var string $time_start;
    var string $time_end;
    var string $callsign;
    var bool $training;
    var bool $event;

    function __construct(string $name, string $time_start, string $time_end, string $callsign, bool $training, bool $event)
    {
        $this->name = $name . "";
        $this->time_start = substr($time_start, 0, -8);
        $this->time_end = substr($time_end, 0, -8);
        $this->callsign = $callsign;
        $this->abbreviation = $this->create_abbreviation($name);
        $this->training = $training;
        $this->event = $event;
    }

    function create_abbreviation($name) : string
    {
        $str_arr = explode(" ", $name);
        return mb_substr($str_arr[0], 0, 2) . mb_substr(end($str_arr), 0, 2);
    }
}
