<?php
// B.H.

/**
 * @desc Base class for model column descriptor
 * @author mkaganer
 */
abstract class sql_model_column {

	/**
	 * @var sql_model
	 */
	public $model;

	/**
	 * @desc Column's name
	 * @var string
	 */
	public $name;

	/**
	 * @desc Column's config parameters
	 * @var array
	 */
	public $params;


	/**
	 * @desc Get this column's value from the row
	 * @param sql_model_data_row $data_row
	 * @return mixed
	 */
	public abstract function get_data($data_row);

	/**
	 * @desc Set this column's value into the data row
	 * @param sql_model_data_row $data_row
	 * @param $value
	 */
	public abstract function set_data($data_row,$value);

}
?>