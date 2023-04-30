<?php

/**
 * PHP MVC Framework
 *
 * A simple PDO interface to make database functions more accessible.
 *
 * @author Tolga Akyol
 * 
 */

namespace System;

use PDOException;

class Database extends \PDO
{
    protected \PDOStatement $stmt;

    protected function __construct()
    {
        $hostname   = DB_HOST;
        $username   = DB_USER;
        $password   = DB_PASS;
        $dbname     = DB_NAME;

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

    protected function sqlQuery($sql)
    {
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
    protected function bind($key, $value, $type = null)
    {
        if(!isset($this->stmt))
        {
            die('SQL statement not defined.'); // TODO
        }

        if (is_null($type))
        {
            switch (true)
            {
                case is_int($value):
                    $type = parent::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = parent::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = parent::PARAM_NULL;
                    break;
                default:
                    $type = parent::PARAM_STR;
            }
        }

        $this->stmt->bindValue($key, $value, $type);
    }

    // Requires a prepared SQL statement before calling.
    protected function execute()
    {
        return $this->stmt->execute();
    }

    // Requires a prepared SQL statement before calling.
    protected function resultSet($method = parent::FETCH_ASSOC)
    {
        $this->execute();
        return $this->stmt->fetchAll($method);
    }

    // Requires a prepared SQL statement before calling.
    protected function single($method = parent::FETCH_ASSOC)
    {
        $this->execute();
        return $this->stmt->fetch($method);
    }

    // Requires a prepared SQL statement before calling.
    protected function rowCount()
    {
        return $this->stmt->rowCount();
    }
    
}