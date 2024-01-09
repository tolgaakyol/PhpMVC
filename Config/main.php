<?php

namespace TolgaAkyol\PhpMVC\Config;

use TolgaAkyol\PhpMVC\Application;

$fieldsAllowOverride = array(
  'SITE_NAME',
  'URL_FULL',
  'URL_ROOT',
  'PACKAGE_PREFIX',
  'INDEX_CONTROLLER',
  'INDEX_METHOD',
  'TIMEZONE',
  'ERROR_REPORTING',
  'HTTPS_ENABLED',
  'DB_HOST',
  'DB_NAME',
  'DB_USER',
  'DB_PASS',
  'LOGIN_WITH',
  'REQUIRE_EMAIL_ACTIVATION',
  'ROLE_CHANGE_REQ_LOGIN',
  'MULTI_SESSION_LIMIT',
  'USERNAME_LENGTH_MIN',
  'USERNAME_LENGTH_MAX',
  'PASSWORD_LENGTH_MIN',
  'PASSWORD_LENGTH_MAX',
  'USE_CORE_AUTH_VIEWS',
  'USE_CORE_INDEX_PAGE',
  'USE_RECAPTCHA',
  'RECAPTCHA_SITE_KEY',
  'RECAPTCHA_SECRET_KEY',
  'DIR_CONFIG',
  'DIR_SYSTEM',
  'DIR_CONTROLLERS',
  'DIR_MODELS',
  'DIR_VIEWS',
  'DIR_HELPERS',
  'DIR_LOGS',
  'DIR_PUBLIC',
  'CACHE_LIFETIME'
);

foreach($fieldsAllowOverride as $field) {
  try {
    define($field, constant(Application::getCustomConfig() . $field));
  } catch (\Error $e) {
    define($field, constant('TolgaAkyol\PhpMVC\Config\\' . $field . '_DEFAULT'));
  }
}

# Timezone
defined('TIMEZONE') && date_default_timezone_set(constant('TIMEZONE'));

# User levels & authentication
enum UserLevels: int {
  case Inactive = 0;
  case Standard = 1;
  case Admin = 2;
}

enum TokenUseCase: int {
  case Activation = 1;
  case ResetPassword = 2;
}

if(constant('REQUIRE_EMAIL_ACTIVATION')) {
  define('DEFAULT_USER_LEVEL', UserLevels::Inactive->value);
} else {
  define('DEFAULT_USER_LEVEL', UserLevels::Standard->value);
}