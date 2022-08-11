<?php

namespace System;

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
    public static function toFile($type, $caller, $message)
    {
        switch ($type)
        {
            case LogType::Error:
                $type = 'ERROR';
                break;
            case LogType::Info:
                $type = 'INFO';
                break;
        }

        // TODO - create daily log files by checking the current date and naming the log file accordingly
        
        $date = '[' . date("Y-m-d H:i:s") . ']';
        $file = DIR_LOGS . strtolower($type) . '.log';

    }
}