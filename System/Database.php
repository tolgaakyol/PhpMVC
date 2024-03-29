<?php

/**
 * PHP MVC Framework
 *
 * A simple PDO interface to make database functions more accessible.
 *
 * @author Tolga Akyol
 *
 */

namespace TolgaAkyol\PhpMVC\System;

use Exception;
use PDO;
use PDOException;
use PDOStatement;

class Database extends PDO
{
  protected PDOStatement $stmt;

  protected function __construct()
  {
    try {
      $hostname   = constant('DB_HOST');
      $username   = constant('DB_USER');
      $password   = constant('DB_PASS');
      $dbname     = constant('DB_NAME');

      $dsn = "mysql:host=$hostname;dbname=$dbname;charset=UTF8";

      $options = array(
          parent::ATTR_ERRMODE => parent::ERRMODE_EXCEPTION
      );

      parent::__construct($dsn, $username, $password, $options);
    } catch (PDOException $e) {
      Log::toFile(LogType::Critical, __METHOD__, 'DB connection failed: ' . $e->getMessage());
      Controller::systemError(__METHOD__, 'DB connection failed: ' . $e->getMessage());
    } catch (Exception $e) {
      Log::toFile(LogType::Critical, __METHOD__, 'Constant not defined: ' . $e->getMessage());
      Controller::systemError(__METHOD__, 'Constant not defined: ' . $e->getMessage());
    }
  }

  protected function sqlQuery($sql): void
  {
    try {
      $this->stmt = $this->prepare($sql);
    } catch (PDOException $e) {
      Controller::systemError(__METHOD__, 'Could not successfully prepare the SQL stmt: ' . $e->getMessage());
    }
  }

  // Requires a prepared SQL statement before calling.
  protected function bind($key, $value, $type = null): void
  {
    if (!isset($this->stmt)) {
      Controller::systemError(__METHOD__, 'SQL statement not defined.');
    }

    if (is_null($type)) {
      $type = match (true) {
        is_int($value) => parent::PARAM_INT,
        is_bool($value) => parent::PARAM_BOOL,
        is_null($value) => parent::PARAM_NULL,
        default => parent::PARAM_STR,
      };
    }

    $this->stmt->bindValue($key, $value, $type);
  }

  // Requires a prepared SQL statement before calling.
  protected function execute(): bool
  {
    return $this->stmt->execute();
  }

  // Requires a prepared SQL statement before calling.
  protected function resultSet($method = parent::FETCH_ASSOC): array|false
  {
    $this->execute();
    return $this->stmt->fetchAll($method);
  }

  // Requires a prepared SQL statement before calling.
  protected function single($method = parent::FETCH_ASSOC): mixed
  {
    $this->execute();
    return $this->stmt->fetch($method);
  }

  // Requires a prepared SQL statement before calling.
  protected function rowCount(): int
  {
    return $this->stmt->rowCount();
  }

}