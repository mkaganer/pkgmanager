<?php
// B.H.

/**
 * @desc
 * @author mkaganer
 */
abstract class sql_model {

	/**
	 * @desc A connection for this model
	 * @var sql_connection
	 */
	public $link;

	/**
	 * @desc Model's name as provided by sql_connection::get_model()
	 * @var string
	 */
	public $name;

	/**
	 * @desc Associative array of all defined columns in the model
	 * @var array of sql_column
	 */
	public $columns;


}
?>