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
        $file = DIR_LOGS . $date->format('Y-m') . '.log';

        // Format the string that will be passed into the log file.        
        $caller = str_pad($caller, 60, " ");
        $type = strtoupper(str_pad($type->name, 10, " "));
        $log = $date->format('Y-m-d H:i:s') . "\t\t" . $type . " $caller " . $message . "\n"; // FIXME - Format $message in a way that the lines will include multiple aligned columns

        error_log($log, 3, $file);
    }
}