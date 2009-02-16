<?php
// B.H.

abstract class html_form_elm extends html_element {

  public $type;
  public $name;
  public $param;
  
  public function __construct() {
    if (isset($this->param['class'])) $this->attr['class'] = $this->param['class'];
  }
  
  // true if this class is a submit button (triggers postback binding)
  abstract public function is_submit();

  // returns a value that was submitted for the control
  public function postback_value($method) {
    switch($method) {
    case 'get':
      return (isset($_GET[$this->name]))?stripslashes($_GET[$this->name]):null;
    case 'post':
      return (isset($_POST[$this->name]))?stripslashes($_POST[$this->name]):null;
    }
  }
  
  public function init($parent=null) {
    parent::init($parent);
    if (is_null($this->form_context)) {
      //var_dump($this);
      throw new Exception("form_context is null with form elements!");
    }
    $this->form_context->attach_control($this);
  }
  
  public function value() {
    if (!$this->init_done) throw new Exception("Object was not initialized!");
    return isset($this->form_context->data[$this->name])?
      $this->form_context->data[$this->name]:null;
  }

}
?>