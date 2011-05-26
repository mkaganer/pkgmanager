<?php
// B.H.

/**
 * @desc Base class for SQL filter builders
 * @author mkaganer
 */
abstract class sql_filter {
    /**
     * @var sql_connection
     */
    public $link;

    /**
     * @desc Operator to use for condition grouping
     * @var string
     */
    public $operator = "AND";

    /**
     * @desc If not empty, this value will be used as table's or DB's alias in all column names
     * i.e. t1.id
     * @var string
     */
    public $prefix = "";

    /**
     * @desc Enclose the result in () parences
     * @var boolean
     */
    public $outer_parences = true;

    /**
     * @desc Enclose each condition in () parences
     * @var boolean
     */
    public $inner_parences = true;

    /**
     * @desc Array that represents filter conditions list. Format example:
     * Each key is a column name, optionaly prepended with operator code
     * Allowed codes are: (= is default if ommited)
     *   =,<,>,<=,>=,<> - obvious
     *   ~ - like
     *   =0 - is null, =1 - is not null
     *   # - in (value must be array)
     *   #! - not in (value must be array)
     *   = with array as value is translated to #
     *   <> with array as value is translated to #!
     * If ! is a first character (before operator) then the value is not escaped but considered
     * to be SQL expression.
     * Values with int keys will be added to the output "as is".
     * For values that are objects, __toString() method will be called.
     * Example:
     *   array(
     *     '<=price' => 100, '>=price' => 50,
     *     'parent_id' => 0, '~=title' => '%flag%',
     *     '!<time' => 'now()', '#category_id' => array(1,2,3)
     *   )
     *   Will produce the folowing SQL:
     *   ( `price` <= 100 AND `price` >= 50 AND `parent_id` = 0 AND
     *     `title` like '%flag%' AND `time` < now() AND
     *     `category_id` in (1,2,3) )
     * @var array
     */
    public $filters;

    public abstract function render();

    public abstract function __toString();
}
?>