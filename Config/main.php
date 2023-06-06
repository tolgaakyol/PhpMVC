<?php

namespace Tolgaakyol\PhpMVC\Config;

# Main config
const SITE_NAME = 'PhpMVC';
const URL_FULL = 'http://mvc.local/';
const URL_ROOT = 'mvc.local';
const PACKAGE_PREFIX = 'Tolgaakyol\PhpMVC\\';
const DEFAULT_LANGUAGE = 'en';
const DEFAULT_CONTROLLER = 'Page';
const DEFAULT_METHOD = 'home';
const ERROR_REPORTING = E_ALL;
const HTTPS_ENABLED = false;

# User authentication related
const LOGIN_WITH = 'username';
const REQUIRE_EMAIL_ACTIVATION = true;
const ROLE_CHANGE_REQ_LOGIN = false;
const MULTI_SESSION_LIMIT = 2;
const USERNAME_LENGTH_MIN = 3;
const USERNAME_LENGTH_MAX = 20;
const PASSWORD_LENGTH_MIN = 6;
const PASSWORD_LENGTH_MAX = 30;

# Timezone
date_default_timezone_set("Etc/GMT-3");