<?php

namespace TolgaAkyol\PhpMVC\Helpers;

enum ErrorType {
  case UserWrongCredentials;
  case UserFailedRecaptcha;
  case UserEmailExists;
  case UserNameExists;
  case UserEmailNotFound;
  case UserInvalidToken;
  case UserEmptyToken;
  case UserExpiredToken;
}