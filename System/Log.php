<?php

namespace System;

use DateTime;

enum LogType
{
    case Error;
    case Info;
    case Notice;
    case Warning;
    case Critical;
}

class Log
{
    public static function toFile(LogType $type, string $caller, string $message)
    {        
        $date = new DateTime("now");

        // File name based on the log type and the date.
        $file = DIR_LOGS . strtolower($type->name) . "." . $date->format('Y-m') . '.log';

        // Format the string that will be passed into the log file.
        $log = $date->format('Y-m-d H:i:s') . " $caller " . " $message " . "\n"; // FIXME - Format $message in a way that the lines will include multiple aligned columns

        error_log($log, 3, $file);
    }
}