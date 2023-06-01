<?php

enum UserLevels: int {
  case Inactive = 0;
  case Standard = 1;
  case Admin = 2;
}

enum NonceUseCase: int {
  case Activation = 1;
  case ResetPassword = 2;
}

if(REQUIRE_EMAIL_ACTIVATION) {
  define('DEFAULT_USER_LEVEL', UserLevels::Inactive->value);
} else {
  define('DEFAULT_USER_LEVEL', UserLevels::Standard->value);
}