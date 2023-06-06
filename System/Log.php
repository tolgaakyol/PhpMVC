<?php

namespace TolgaAkyol\PhpMVC\System;

use DateTime;
use TolgaAkyol\PhpMVC\Config as Config;

enum LogType
{
    case Info;
    case Error;
    case Warning;
    case Critical;
}

class Log
{
    /**
     * @param LogType $type Select one from the LogType enum. (Options are: Info, Error, Warning, Critical)
     * @param string $caller Simply pass in the PHP's __METHOD__ magic constant. (60 chars max.)
     * @param string $message Pass in the message that you want to log.
     * @return bool Returns true if successful.
     */
    public static function toFile(LogType $type, string $caller, string $message): bool
    {        
        $date = new DateTime("now");

        // File name based on the log type and the date.
        $file = Config\DIR_LOGS . $date->format('Y-m') . '.log';

        // Clamp the string inputs
        $caller = substr($caller, 0, 60);
        $caller = str_pad($caller, 60, " ");
        $type = strtoupper(str_pad($type->name, 10, " "));
        $message = substr($message, 0, 255);
        
        // Format the string that will be passed into the log file.
        $log = $date->format('Y-m-d H:i:s') . "\t\t" . $type . "\t\t". "$caller " . $message . "\n"; // FIXME - Format $message in a way that the lines will include multiple aligned columns

        if(error_log($log, 3, $file)){ return true; } else { return false; }
    }
}