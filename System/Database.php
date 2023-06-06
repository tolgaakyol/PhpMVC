<?php

/**
 * PHP MVC Framework
 *
 * A simple PDO interface to make database functions more accessible.
 *
 * @author Tolga Akyol
 * 
 */

namespace Tolgaakyol\PhpMVC\System;

use PDO;
use PDOException;
use PDOStatement;
use Tolgaakyol\PhpMVC\Config as Config;

class Database extends PDO
{
    protected PDOStatement $stmt;

    protected function __construct() {
        $hostname   = Config\DB_HOST;
        $username   = Config\DB_USER;
        $password   = Config\DB_PASS;
        $dbname     = Config\DB_NAME;

        $dsn = "mysql:host=$hostname;dbname=$dbname;charset=UTF8";

        $options = array(
            parent::ATTR_ERRMODE => parent::ERRMODE_EXCEPTION
        );

        try
        {
            parent::__construct($dsn, $username, $password, $options);
        }
        catch (PDOException $e)
        {
            die('DB connection failed: ' . $e->getMessage()); // TODO - DB connection error
        }                
    }

    protected function sqlQuery($sql): void {
        try
        {
            $this->stmt = $this->prepare($sql);
        }
        catch (PDOException $e)
        {
            die('Could not successfully prepare the SQL stmt: ' . $e->getMessage()); // TODO - sql stmt preapare error
        }
    }

    // Requires a prepared SQL statement before calling.
    protected function bind($key, $value, $type = null): void {
        if(!isset($this->stmt))
        {
            die('SQL statement not defined.'); // TODO
        }

        if (is_null($type))
        {
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
    protected function execute(): bool {
        return $this->stmt->execute();
    }

    // Requires a prepared SQL statement before calling.
    protected function resultSet($method = parent::FETCH_ASSOC): array|false {
        $this->execute();
        return $this->stmt->fetchAll($method);
    }

    // Requires a prepared SQL statement before calling.
    protected function single($method = parent::FETCH_ASSOC): mixed {
        $this->execute();
        return $this->stmt->fetch($method);
    }

    // Requires a prepared SQL statement before calling.
    protected function rowCount(): int {
        return $this->stmt->rowCount();
    }
    
}