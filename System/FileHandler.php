<?php

namespace TolgaAkyol\PhpMVC\System;

use JetBrains\PhpStorm\NoReturn;

class FileHandler
{
  private string $basePath;

  public function __construct($basePath) {
    if (!is_dir($basePath)) {
      die('Invalid base path');
    }
    $this->basePath = rtrim($basePath, '/') . '/';
  }

  #[NoReturn]
  public function downloadFile($fileName, $customFileName = null): void {
    $filePath = $this->basePath . $fileName;

    if (file_exists($filePath)) {
      // Set Expires header for caching
      header('Expires: ' . gmdate('D, d M Y H:i:s', time() + constant('CACHE_LIFETIME')) . ' GMT');
      header('Content-Description: File Transfer');
      header('Content-Type: application/octet-stream');
      header('Pragma: public');
      header('Content-Length: ' . filesize($filePath));

      if(!is_null($customFileName)) {
        header('Content-Disposition: attachment; filename="' . $customFileName . '"');
      }

      readfile($filePath);
      exit;
    } else {
      $this->handleFileError($filePath);
    }
  }

  public function getFileFormat($fileName) {
    $filePath = $this->basePath . $fileName;

    if (file_exists($filePath)) {
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $fileFormat = finfo_file($finfo, $filePath);
      finfo_close($finfo);

      return $fileFormat;
    } else {
      $this->handleFileError($filePath);
    }
  }

  public function checkFileSize($fileName, $maxSize): bool {
    $filePath = $this->basePath . $fileName;

    if (file_exists($filePath) && filesize($filePath) <= $maxSize) {
      return true;
    } else {
      return false;
    }
  }

  public function isAllowedMimeType($fileName, $allowedMimeTypes): bool {
    $fileFormat = $this->getFileFormat($fileName);

    if (in_array($fileFormat, $allowedMimeTypes)) {
      return true;
    } else {
      return false;
    }
  }

  #[NoReturn]
  private function handleFileError($filePath): void {
    Log::toFile(LogType::Info, __METHOD__, 'Error accessing file: ' . $filePath);
    Controller::systemError('Error accessing file: ' . $filePath, __METHOD__);
  }
}