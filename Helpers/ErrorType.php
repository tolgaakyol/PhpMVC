<?php

namespace TolgaAkyol\PhpMVC\Helpers;

enum ErrorType: int {
  case UserWrongCredentials = 1001;
  case UserFailedRecaptcha = 1002;
  case UserEmailExists = 1003;
  case UserNameExists = 1004;
  case UserEmailNotFound = 1005;
  case UserInvalidToken = 1006;
  case UserEmptyToken = 1007;
  case UserExpiredToken = 1008;
}