<?php
// B.H.

class html_form extends html_element {
  // any arguments after the 2 first are passed to html_block::__construct()
  public function __construct($action,$method='post',$file_upload=false,$attr=null) {
    parent::__construct('form',$attr);
    $this->form_context = new html_form_context($method);
    $this->attr['action'] = $action;
    $this->attr['method'] = $method;
    if ($file_upload) $this->attr['enctype']="multipart/form-data";
    if (func_num_args()>4) {
      $args = func_get_args();
      array_shift($args); array_shift($args); array_shift($args); array_shift($args);
      $this->add($args);
    }
  }
}
?>