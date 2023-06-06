<?php

/**
 * 
 * PHP MVC Framework
 *
 * Parent class for models that includes simple automated CRUD operations.
 *
 * @author Tolga Akyol
 * @see TolgaAkyol\PhpMVC\Helpers\SQLWhere
 *   
 */

namespace TolgaAkyol\PhpMVC\System;

class Model extends Database
{
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * A method to select record(s) from the database. Only suitable for simple requests, therefore, complex statements should be created manually.
     * @param string $table Name of the table from which the record(s) will be fetched.
     * @param string|array $columns Selected columns to look for. Either a string or an array can be passed. To select all columns inside the table(s), simply pass '*' within a string.
     * @param string|null $where Conditions for the SQL statement must be stated here. Create an object of type 'SQLWhere', use its methods to crete a WHERE clause, then pass the object here using its 'stmt()' method.
     * @param array|null $values An array of values to be passed to the prepared statement. Based on the statement passed in the $where parameter, an array must be passed here with the following format: array(':column_name' => 'value', ':column_name' => 'value', ...). Had an SQLWhere object created earlier, simply pass the object's 'values()' method here.
     * @param array|null $joins An array of tables to be joined. The array must be in the following format: array(array('table_name', 'join_type', 'join_condition'), array('table_name', 'join_type', 'join_condition'), ...).
     * @param string|null $orderBy Allows the results to be sorted based on the selection. Simply pass in the name of the column that the sorting will be based upon, and the direction (ASC|DESC), with a whitespace in between (e.g. "columnName ASC").
     * @param bool $distinct If passed in true, duplicate records will be omitted. Default value is false.
     * @param int $fetchMode Default is PDO::FETCH_ASSOC. It can be overridden by passing the desired mode herein.
     * @return mixed Returns the selected record(s). Data type will be based on the returning value and the fetch mode.;
     */
    protected function select(string $table, string|array $columns, string $where = null, array $values = null, array $joins = null, string $orderBy = null, bool $distinct = false, int $fetchMode = parent::FETCH_ASSOC): mixed
    {
        // FIXME: Allow selection of multiple tables?

        $operator           = $distinct ? "SELECT DISTINCT " : "SELECT ";
        $from               = " FROM $table";
        $selectedColumns    = '';
        $order              = '';
        $join               = '';

        // Protection against empty & incorrect arguments
        if (empty($table)) { die('Must declare at least one table name.'); } // ERRMSG
        if (empty($columns)) { die('Must declare the columns to select!'); } // ERRMSG
        if (!is_null($joins) && !is_array($joins)) { die("'Join' must be an array in the following format: " . "array(array('table_name', 'join_type', 'join_condition'), array('table_name', 'join_type', 'join_condition'), ...)"); } // ERRMSG

        // Set columns
        if (is_array($columns))
        {
            $selectedColumns = implode(', ', $columns);
        }
        else
        {
            $selectedColumns = $columns;
        }

        // Set join(s)
        if (!is_null($joins))
        {
            foreach ($joins as $j)
            {
                $join .= " $j[1] JOIN $j[0] ON $j[2]";
            }
        }

        // Set ordering
        if (!empty($orderBy))
        {
            $order = " ORDER BY $orderBy";
        }

        // Prepare and execute the sql stmt
        $sql = $operator . $selectedColumns . $from . $join . $where . $order;   
        $this->stmt = $this->prepare($sql);

        // Check if conditions are passed but values are not
        if (!empty($where) && empty($values))
        {
            die("Values cannot be empty when conditions are set."); // ERRMSG
        }

        // If there are any values passed, bind them to their keys
        if(!empty($values))
        {
            foreach($values as $key => $value)
            {
                $this->bind($key, $value);
            }
        }

        $this->stmt->execute();
        return $this->stmt->fetchAll($fetchMode);
    }

    /**
     * A method to simplify the insertion of new records into the database.
     * @param string $table Name of the table in which the update will take place.
     * @param array $content An array with pairs of column names and desired values. The format should be: ['columnName' => 'newValue', ...]
     * @return bool Returns true if the operation was carried out successfully.
     */
    protected function insert(string $table, array $content): bool
    {
        $columns        = implode(', ', array_keys($content));
        $placeholders   = ':' . implode(', :', array_keys($content));

        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $this->stmt = $this->prepare($sql);

        foreach ($content as $key => $value)
        {
            $this->bind(":$key", $value);
        }
        
        return $this->stmt->execute();
    }    

    /**
     * A method to simplify the updating of the database records.
     * @param string $table Name of the table in which the update will take place.
     * @param array $content An array with pairs of column names and desired values. The format should be: ['columnName' => 'newValue', ...]
     * @param string $where Conditions for the SQL statement must be stated here. Create an object of type 'SQLWhere', use its methods to crete a WHERE clause, then pass the object here using its 'stmt()' method.
     * @param array $values An array of values to be passed to the prepared statement. Based on the statement passed in the $where parameter, an array must be passed here with the following format: array(':column_name' => 'value', ':column_name' => 'value', ...). Had an SQLWhere object created earlier, simply pass the object's 'values()' method here.
     * @return bool Returns true if the operation was carried out successfully.
     */
    protected function update(string $table, array $content, string $where, array $values): bool
    {
        // Manually the binding values of the where clause to prevent any overlaps with the $content
        foreach($values as $key => $value)
        {
            $where = str_replace(":$key", $this->quote($value), $where);
        }

        $columnsToUpdate = null;
        foreach ($content as $key => $value)
        {
            $columnsToUpdate .= "$key=:$key,";
        }
        $columnsToUpdate = rtrim($columnsToUpdate, ",");

        $sql = "UPDATE $table SET $columnsToUpdate" . $where;

        $this->stmt = $this->prepare($sql);

        foreach ($content as $key => $value)
        {
            $this->bind(":$key", $value);
        }

        return $this->stmt->execute();
    }

    /**
     * A method to simplify the deletion of the database records.
     * @param string $table Name of the table in which the deletion will take place.
     * @param string $where Conditions for the SQL statement must be stated here. Create an object of type 'SQLWhere', use its methods to crete a WHERE clause, then pass the object here using its 'stmt()' method.
     * @param array $values An array of values to be passed to the prepared statement. Based on the statement passed in the $where parameter, an array must be passed here with the following format: array(':column_name' => 'value', ':column_name' => 'value', ...). Had an SQLWhere object created earlier, simply pass the object's 'values()' method here.
     * @param integer $limit (optional) This method allows deleting multiple records at once. It can be limited by passing the desired amount. Default is 1.
     * @param string|false $orderBy (optional) Pass in the column name by which the records should be ordered.
     * @param bool $desc (optional) While true, records will be ordered as descending. Requires $orderBy to be passed in.
     * @return bool Returns true if the operation was carried out successfully.
     */
    protected function delete(string $table, string $where, array $values, int $limit = 1, string|false $orderBy = false, bool $desc = false): bool
    {
        $sql = "DELETE FROM $table" . $where;

        if($orderBy) {
          $sql .= " ORDER BY " . $orderBy;

          if($desc) {
            $sql .= " DESC";
          }
        }

        if ($limit > 0)
        {
            $sql .= " LIMIT $limit";    
        }

        $this->stmt = $this->prepare($sql);

        foreach ($values as $key => $value)
        {
            $this->bind(":$key", $value);
        }

        return $this->stmt->execute();
    }
}