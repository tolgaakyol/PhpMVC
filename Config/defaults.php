<?php

namespace TolgaAkyol\PhpMVC\Config;

# Main config
const SITE_NAME_DEFAULT = 'PhpMVC';
const URL_FULL_DEFAULT = 'http://mvc.local/';
const URL_ROOT_DEFAULT = 'mvc.local';
const PACKAGE_PREFIX_DEFAULT = 'TolgaAkyol\PhpMVC\\';
const TIMEZONE_DEFAULT = 'Etc/GMT-3';
const INDEX_CONTROLLER_DEFAULT = 'MVC';
const INDEX_METHOD_DEFAULT = 'index';
const ERROR_REPORTING_DEFAULT = E_ALL;
const HTTPS_ENABLED_DEFAULT = false;

# Database related
const DB_HOST_DEFAULT = 'localhost';
const DB_NAME_DEFAULT = 'phpmvc';
const DB_USER_DEFAULT = 'root';
const DB_PASS_DEFAULT = '';

# User authentication related
const LOGIN_WITH_DEFAULT = 'username';
const REQUIRE_EMAIL_ACTIVATION_DEFAULT = true;
const ROLE_CHANGE_REQ_LOGIN_DEFAULT = false;
const MULTI_SESSION_LIMIT_DEFAULT = 2;
const USERNAME_LENGTH_MIN_DEFAULT = 3;
const USERNAME_LENGTH_MAX_DEFAULT = 20;
const PASSWORD_LENGTH_MIN_DEFAULT = 6;
const PASSWORD_LENGTH_MAX_DEFAULT = 30;
const USE_CORE_AUTH_VIEWS_DEFAULT = true;
const USE_CORE_INDEX_PAGE_DEFAULT = true;

# ReCaptcha
const USE_RECAPTCHA_DEFAULT = true;
const RECAPTCHA_SITE_KEY_DEFAULT = '';
const RECAPTCHA_SECRET_KEY_DEFAULT = '';

# Directories
const DIR_CONFIG_DEFAULT = 'Config' . DIRECTORY_SEPARATOR;
const DIR_SYSTEM_DEFAULT = 'System' . DIRECTORY_SEPARATOR;
const DIR_CONTROLLERS_DEFAULT = 'Controllers' . DIRECTORY_SEPARATOR;
const DIR_MODELS_DEFAULT = 'Models' . DIRECTORY_SEPARATOR;
const DIR_VIEWS_DEFAULT = 'Views' . DIRECTORY_SEPARATOR;
const DIR_HELPERS_DEFAULT = 'Helpers' . DIRECTORY_SEPARATOR;
const DIR_LOGS_DEFAULT = 'Logs' . DIRECTORY_SEPARATOR;
const DIR_PUBLIC_DEFAULT = 'Public' . DIRECTORY_SEPARATOR;