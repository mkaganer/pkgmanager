<?php
// B.H.

class html_template extends html_block {

  // any arguments after the first are passed to html_block::__construct()
  public function __construct($tpl_name=null) {
    if (func_num_args()>1) {
      $args = func_get_args(); array_shift($args);
      parent::__construct($args);
    } else {
      parent::__construct();
    }
    if (!empty($tpl_name)) $this->load_template($tpl_name);
  }
  
}
?>