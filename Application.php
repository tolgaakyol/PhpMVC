<?php

namespace TolgaAkyol\PhpMVC;

use TolgaAkyol\PhpMVC\Config as Config;

class Application {
  public static bool $isFrameworkDev;
  public static string $PATH_CORE;
  public static string|bool $PATH_EXT;
  public static string|false $PROJECT_PREFIX;

  public function __construct(bool $mvcDev = false, bool $errorReportingOverride = false, string|bool $projectNamespace = false, string|false $projectDir = false) {
    ini_set('session.use_strict_mode', true);
    session_start();

    self::$isFrameworkDev = $mvcDev;
    if(!$mvcDev && $projectDir) {
      self::$PATH_EXT = $projectDir;
    } else if(!$mvcDev && !$projectDir) {
      self::$PATH_EXT = $this->findPath('index.php', 'src');
    } else {
      self::$PATH_EXT = '';
    }
    self::$PATH_CORE = $mvcDev ? '' : $this->findPath('tolgaakyol\php-mvc\Application.php', 'php-mvc');
    self::$PROJECT_PREFIX = $projectNamespace . '\\';

    !self::$isFrameworkDev && $this->createHtaccess();

    # Load config files
    foreach (glob(self::$PATH_CORE . 'Config/*.php') as $fileName) {
      include $fileName;
    }

    foreach (glob(Config\DIR_HELPERS . '*.php') as $fileName) {
      include $fileName;
    }

    if(!self::$isFrameworkDev && self::$PATH_EXT) {
      foreach (glob( self::$PATH_EXT. 'Config/*.php') as $fileName) {
        include $fileName;
      }

      foreach (glob(self::$PATH_EXT . 'Helpers/*.php') as $fileName) {
        include $fileName;
      }
    }

    # Error reporting
    $errorReportingOverride && error_reporting(Config\ERROR_REPORTING);

    # System autoloader
    spl_autoload_register(function ($class) {
      $class = str_replace('\\', '/', $class);
      $class = str_replace('TolgaAkyol/PhpMVC/', '', $class);
      include_once $class . '.php';
    });

    # Instantiate bootstrap
    new System\Bootstrap;
  }

  private function findPath(string $fileName, $directoryToSelect): string|false {
    $files = get_included_files();
    $path = false;
    foreach($files as $file) {
      if(str_contains($file, $fileName)) {
        $path = $file;
        break;
      }
    }

    if(!$path) {
      return false;
    } else {
      $directories = explode('\\', $path);
      if($directoryToSelect) {
        $vendorKey = array_search($directoryToSelect, $directories);
        $directories = array_splice($directories, 0, $vendorKey+1);
      }
      return implode('/', $directories) . '/';
    }
  }

  private function createHtaccess(): void {
    $path = self::$PATH_EXT . '.htaccess';

    if(file_exists($path)) {
      return;
    }

    $file = fopen(self::$PATH_EXT . '.htaccess', 'w');

    $contents = <<<'EOD'
    RewriteEngine On

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-l 
    
    RewriteRule ^(.+) index.php?url=$l [QSA,L]
    EOD;

    fwrite($file, $contents);
  }
}