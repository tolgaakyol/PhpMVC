<?php

namespace System;

enum Error {
  # Session related errors
  case session_Missing;
  case session_Unauthorized;
  case session_LevelMismatch;
  case session_Corrupt;
  case session_NetworkChanged;
}