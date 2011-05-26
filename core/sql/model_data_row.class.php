<?php
// B.H.

/**
 * @desc
 * @author mkaganer
 */
class sql_model_data_row extends ArrayObject {

	/**
	 * @var sql_model
	 */
	public $model;

	public function __construct($model) {
        parent::__construct(array());
        $this->model = $model;
	}

}
?>