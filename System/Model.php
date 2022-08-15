<?php

/**
 * PHP MVC Framework
 *
 * Model parent class that includes simple automated SQL operations.
 *
 * @author Tolga Akyol
 * 
 */

namespace System;

class Model extends Database
{
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * A method to automatically select a record from the database. Only suitable for simple requests, therefore, complex statements should be created manually.
     * @param mixed $tables Name of the table from which the record(s) will be fetched.
     * @param mixed $columns Selected columns to look for. Either a string or an array can be passed. To select all columns inside the table(s), simply pass '*' within a string.
     * @param string $where Conditions for the SQL statement must be stated here. where() method of this class can be used to generate this string. // FIXME
     * @param array $values Values of the fields which were included in $where. The format should be: ['columnName' => 'value', ...] // FIXME
     * @param string $orderBy Allows the results to be sorted based on the selection. Simply pass in the name of the column that the sorting will be based upon, and the direction (ASC|DESC), with a whitespace in between. (e.g. "columnName ASC")
     * @param bool $distinct If passed in true, duplicate records will be omitted. Default value is false.
     * @param int $fetchMode Default is PDO::FETCH_ASSOC. It can be overridden by passing the desired mode herein.
     * @return mixed Returns the selected record(s). Data type will be based on the returning value and the fetch mode.;
     */
    protected function select($tables, $columns, $where = null, $values = null, $orderBy = null, $distinct = false, $fetchMode = parent::FETCH_ASSOC)
    {
        $operator           = $distinct ? "SELECT DISTINCT " : "SELECT ";
        $selectedColumns    = '';
        $from               = '';
        $order              = '';

        // Set tables
        if (empty($tables))
        {
            die('Must declare at least one table name!');
        }
        else if (is_array($tables))
        {
            $from = ' FROM (' . implode(', ', $tables) . ')';
        }
        else
        {
            $from = " FROM $tables";
        }

        // Set columns
        if (empty($columns))
        {
            die('Must declare columns to select!');
        }
        else if (is_array($columns))
        {
            $selectedColumns = implode(', ', $columns);
        }
        else
        {
            $selectedColumns = $columns;
        }

        // Set ordering
        if (!empty($orderBy))
        {
            $order = " ORDER BY $orderBy";
        }

        // Prepare and execute the sql stmt
        $sql = $operator . $selectedColumns . $from . $where . $order;   
        $this->stmt = $this->prepare($sql);

        // Check if conditions are passed but values are not
        if (!empty($where) && empty($values))
        {
            die('Values cannot be empty when conditions are set.');
        }

        // If there are any values passed, bind them to their keys
        if(!empty($values))
        {
            foreach($values as $key => $value)
            {
                $this->bind(":$key", $value);
            }
        }

        $this->stmt->execute();
        return $this->stmt->fetchAll($fetchMode);
    }

    /**
     * A method to automatically generate WHERE conditions for the SQL statement. Must be called repeatedly for each condition.
     * @param string $column Name of the table's column for which a condition will be generated.
     * @param string $existingStmt When called for the first time, $existingStmt must be null. Consecutive callings should include the previously returned string passed in the $existingStmt.
     * @param string $operator Select the operator if applicaple (e.g. 'AND', 'OR', 'NOT', 'AND NOT'). Default is null;
     * @return string Returns only the " WHERE ... " part of the statement as a string. This can be used within other methods.
     */
    protected function where($column, $existingStmt = null, $operator = null): string
    {
        if (!empty($existingStmt))
        {
            $currentStmt = $existingStmt;

            switch ($operator)
            {
                case 'AND':
                    $currentStmt .= ' AND ' . "$column=:$column";
                    break;
                case 'OR':
                    $currentStmt .= ' OR ' . "$column=:$column";
                    break;
                case 'AND NOT':
                    $currentStmt .= ' AND NOT ' . "$column=:$column";
                    break;                    
            }
        }
        else
        {
            $currentStmt = $operator == 'NOT' ? ' WHERE NOT ' : ' WHERE ';
            $currentStmt .= "$column=:$column";
        }

        return $currentStmt;
    }

    /**
     * A method to simplify the insertion of new records into the database.
     * @param string $table Name of the table in which the update will take place.
     * @param array $content An array with pairs of column names and desired values. The format should be: ['columnName' => 'newValue', ...]
     * @return bool Returns true if the operation was carried out successfully.
     */
    protected function insert($table, $content)
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
     * @param string $where Conditions for the SQL statement must be stated here. where() method of this class can be used to generate this string.
     * @param array $values Values of the fields which were included in $where. The format should be: ['columnName' => 'value', ...]
     * @return bool Returns true if the operation was carried out successfully.
     */
    protected function update($table, $content, $where, $values)
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
     * @param string $table Name of the table in which the update will take place.
     * @param string $where Conditions for the SQL statement must be stated here. where() method of this class can be used to generate this string.
     * @param array $values Values of the fields which were included in $where. The format should be: ['columnName' => 'value', ...]
     * @param integer $limit This method allows deleting multiple records at once. It can be limited by passing the desired amount. Default is 1.
     * @return bool Returns true if the operation was carried out successfully.
     */
    protected function delete($table, $where, $values, $limit = 1)
    {
        $sql = "DELETE FROM $table" . $where;

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

// FIXME
class SqlWhere {
    
    private $currentStmt;

    public function __construct($column, $operator, $value, $not = null)
    {
        $this->currentStmt = is_null($not) ? " WHERE $column" : " WHERE NOT $column";
        $this->operatorSelector($operator, $value);
        return $this;
    }

    public function and($column, $operator, $value){
        $this->currentStmt .= " AND $column";
        $this->operatorSelector($operator, $value);
        return $this;
    }

    public function or($column, $operator, $value)
    {
        $this->currentStmt .= " OR $column";
        $this->operatorSelector($operator, $value);
    }

    public function andnot($column, $operator, $value)
    {
        $this->currentStmt .= " AND NOT $column";
        $this->operatorSelector($operator, $value);
    }

    public function get()
    {
        return $this->currentStmt;
    }

    private function operatorSelector($operator, $value)
    {
        switch ($operator)
        {
            case '=':
            case '>':
            case '<':
            case '>=':
            case '<=':
            case '<>':
            case 'LIKE':
                if (is_array($value) && count($value) > 1) { die('Only one value must be provided inside the where clause for a single condition.'); } // ERRMSG
                $this->currentStmt .= " $operator " . $value;
                break;
            case 'BETWEEN':
                if (!is_array($value) || count($value) != 2) { die('In order to use the BETWEEN operator, 2 values must be provided in an array.'); } // ERRMSG
                $this->currentStmt .= ' BETWEEN ' . $value[0] . ' AND ' . $value[1];
                break;
            case 'IN':
            case 'NOT IN':
                if(is_array($value))
                {
                    $this->currentStmt .= $operator == 'IN' ? " IN (" : " NOT IN (";
                    for ($i = 0; $i < count($value); $i++)
                    {
                        $this->currentStmt .= "$i, ";
                    }
                    $this->currentStmt = rtrim($this->currentStmt, ", ") . ")";
                }
                else
                {
                    $operator = 'IN' ? $this->currentStmt .= " IN (" : $this->currentStmt .= " NOT IN (";
                    $this->currentStmt .= $value . ")";
                }
                break;
        }
    }
}