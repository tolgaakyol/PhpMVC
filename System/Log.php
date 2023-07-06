<?php

namespace TolgaAkyol\PhpMVC\System;

use DateTime;
use Exception;
use TolgaAkyol\PhpMVC\Application;

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

    try {
      // File name based on the log type and the date.
      $fileName = $date->format('Y-m') . '.log';
      $filePath = Log::checkLogFile($fileName);
    } catch (Exception) {
      return false;
    }

    // Clamp the string inputs
    $caller = substr($caller, 0, 60);
    $caller = str_pad($caller, 60, " ");
    $type = strtoupper(str_pad($type->name, 10, " "));
    $message = substr($message, 0, 255);

    // Format the string that will be passed into the log file.
    $log = $date->format('Y-m-d H:i:s') . "\t\t" . $type . "\t\t" . "$caller " . $message . "\n"; // FIXME - Format $message in a way that the lines will include multiple aligned columns

    if (error_log($log, 3, $filePath)) {
      return true;
    } else {
      return false;
    }
  }

  private static function checkLogFile($name): string
  {
    $pathDir = Application::$PATH_CORE . constant('DIR_LOGS');
    if (!file_exists($pathDir)) {
      mkdir($pathDir, 0744);
    }

    $pathFile = $pathDir . $name;
    if (!file_exists($pathFile)) {
      fopen($pathFile, 'c');
    }

    return $pathFile;
  }
}

enum LogType
{
  case Info;
  case Error;
  case Warning;
  case Critical;
}