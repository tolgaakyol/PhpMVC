<?php

namespace TolgaAkyol\PhpMVC;

use Exception;

class Application {
  public static bool $frameworkDevMode;
  public static string $PATH_CORE;
  public static string|bool $PATH_EXT;
  public static string|false $PROJECT_PREFIX;

  public function __construct(bool $mvcDev = false, bool $errorReportingOverride = false, string|bool $projectNamespace = false, string|false $projectDir = false) {
    ini_set('session.use_strict_mode', true);
    session_start();

    self::$frameworkDevMode = $mvcDev;
    if(!$mvcDev && $projectDir) {
      self::$PATH_EXT = $projectDir  . DIRECTORY_SEPARATOR;
    } else if(!$mvcDev && !$projectDir) {
      self::$PATH_EXT = $this->findPath('index.php', 'src');
    } else {
      self::$PATH_EXT = '';
    }
    self::$PATH_CORE = $mvcDev ? '' : $this->findPath('tolgaakyol' . DIRECTORY_SEPARATOR . 'php-mvc' . DIRECTORY_SEPARATOR . 'Application.php', 'php-mvc');
    self::$PROJECT_PREFIX = $projectNamespace . '\\';

    !self::$frameworkDevMode && $this->createHtaccess();

    # Load config files
    if(!self::$frameworkDevMode && self::$PATH_EXT) {
      foreach (glob( self::$PATH_EXT. 'Config' . DIRECTORY_SEPARATOR . '*.php') as $fileName) {
        include $fileName;
      }

      foreach (glob(self::$PATH_EXT . 'Helpers' . DIRECTORY_SEPARATOR . '*.php') as $fileName) {
        include $fileName;
      }
    }

    foreach (glob(self::$PATH_CORE . 'Config' . DIRECTORY_SEPARATOR . '*.php') as $fileName) {
      include $fileName;
    }

    try {
      foreach (glob(constant('DIR_HELPERS') . '*.php') as $fileName) {
        include $fileName;
      }

      # Error reporting
      $errorReportingOverride && error_reporting(constant('ERROR_REPORTING'));
    } catch (Exception) {
      die('Unable to proceed due to system error');
    }

    # System autoloader
    spl_autoload_register(function ($class) {
      $class = str_replace('TolgaAkyol\PhpMVC\\', '', $class);
      $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
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
      $directories = explode(DIRECTORY_SEPARATOR, $path);
      if($directoryToSelect) {
        $vendorKey = array_search($directoryToSelect, $directories);
        $directories = array_splice($directories, 0, $vendorKey+1);
      }
      return implode(DIRECTORY_SEPARATOR, $directories) . DIRECTORY_SEPARATOR;
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
    
    # Stop processing if already in the /public directory
    RewriteRule ^Public/ - [L]
    
    # Static resources if they exist
    RewriteCond %{DOCUMENT_ROOT}/Public/$1 -f
    RewriteRule (.+) Public/$1 [L]

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-l 
    
    RewriteRule ^(.+) index.php?url=$l [QSA,L]
    EOD;

    fwrite($file, $contents);
  }

  public static function getCustomConfig(): string|false {
    if(self::$frameworkDevMode || !file_exists(self::$PATH_EXT.'Config' . DIRECTORY_SEPARATOR . 'main.php')) {
      return false;
    } else {
      return self::$PROJECT_PREFIX . 'Config\\';
    }
  }
}