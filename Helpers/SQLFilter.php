<?php 

/**
 * 
 * PHP MVC Framework
 *
 * A helper class that includes methods to simplify the generation of SQL 'WHERE' conditions.
 *
 * @author Tolga Akyol
 * @see TolgaAkyol\PhpMVC\System\Model
 *   
 */

namespace TolgaAkyol\PhpMVC\Helpers;

use TolgaAkyol\PhpMVC\System\Controller;

class SQLFilter {
    
    private string $currentStmt;
    private array $currentPairs = array();

    /**
     * Once the object is created using the constructor, further conditions can be applied by chaining the 'and()', 'or()' and 'andnot()' methods to the stored instance.
     * 
     * Use getStmt() to get the generated SQL 'WHERE' clause.
     * Use getValues() to get the prepared statement => value pairs of the generated SQL 'WHERE' clause.
     * 
     * Example of use:
     * $where = new SQLWhere("column_name", "=", "value");
     * $where->and("column_name", "=", "value")->andnot("column_name_2", ">", "value_2")->or("column_name_3", "<>", "value_3");
     *
     * @param string $column Name of the column for which the condition is being applied.
     * @param string $operator Pass in the comparison operator in a string. Example: '=', '>', '<', '<>', 'LIKE', 'IN', 'BETWEEN', 'IS NULL', 'IS NOT NULL'
     * @param mixed $value Pass in the value to be compared.
     * @param bool $not If true, the generated statement will begin with 'WHERE NOT' instead of 'WHERE'.
     *
     */
    public function __construct(string $column, string $operator, mixed $value, bool $not = false) {
        $this->currentStmt = $not ? " WHERE NOT $column" : " WHERE $column";
        $this->operatorSelector($operator, ":$column");
        $this->currentPairs[$column] = $value;
        return $this;
    }

    /**
     * Usage is identical to the constructor, except that the 'not' parameter is not applicable.
     */
    public function and($column, $operator, $value): static {
        $this->createConditionalStatement(0, $column, $operator, $value);

        return $this;
    }

    /**
     * Usage is identical to the constructor, except that the 'not' parameter is not applicable.
     */
    public function or($column, $operator, $value): static {
        $this->createConditionalStatement(1, $column, $operator, $value);

        return $this;
    }

    /**
     * Usage is identical to the constructor, except that the 'not' parameter is not applicable.
     */
    public function andnot($column, $operator, $value): static {
        $this->createConditionalStatement(2, $column, $operator, $value);

        return $this;
    }

    /**
     * @return string Returns the generated SQL 'WHERE' clause.
     */
    public function getStmt(): string {
        return $this->currentStmt;
    }

    /**
     * @return array Returns the (prepared statement => value) pairs for the generated SQL 'WHERE' clause.
     */
    public function getValues(): array {
        return $this->currentPairs;
    }

    private function createConditionalStatement($condition, $column, $operator, $value): void {
        if (count(array_keys($this->currentPairs)) >= 10)
        {
          Controller::systemError(__METHOD__, 'Maximum number of conditions reached.');
        }

        switch($condition)
        {
            case 0:
                $this->currentStmt .= " AND $column";
                break;
            case 1:
                $this->currentStmt .= " OR $column";
                break;
            case 2:
                $this->currentStmt .= " AND NOT $column";
                break;
            default:
              Controller::systemError(__METHOD__, 'Invalid operation.');
        }

        $column = $this->avoidDuplicateColumns($column);
        $this->operatorSelector($operator, ":$column");

        $this->currentPairs[$column] = $value;
    }

    private function operatorSelector($operator, $value): void
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
                if (is_array($value) && count($value) > 1) { Controller::systemError(__METHOD__, 'Only one value must be provided inside the where clause for a single condition.'); }
                $this->currentStmt .= " $operator " . $value;
                break;
            case 'BETWEEN':
                if (!is_array($value) || count($value) != 2) { Controller::systemError(__METHOD__, "In order to use the BETWEEN operator, 2 values must be provided as an array."); }
                $this->currentStmt .= ' BETWEEN ' . $value[0] . ' AND ' . $value[1];
                break;
            case 'IN':
            case 'NOT IN':
                if(is_array($value))
                {
                    $this->currentStmt .= " $operator (";
                    for ($i = 0; $i < count($value); $i++)
                    {
                        $this->currentStmt .= "$i, ";
                    }
                    $this->currentStmt = rtrim($this->currentStmt, ", ") . ")";
                }
                else
                {
                    $this->currentStmt .= " $operator ($value)";
                }
                break;
            case 'IS NULL':
            case 'IS NOT NULL':
                $this->currentStmt .= " $operator";
                break;
        }
    }

    private function avoidDuplicateColumns($column) {
        if(array_key_exists($column, $this->currentPairs)){
            $suffix = rand(0,100);

            while(array_key_exists($column . "_$suffix", $this->currentPairs)){
                $suffix = rand(0,100);
            }

            return $column . "_$suffix";
        } else {
            return $column;
        }
    }
}