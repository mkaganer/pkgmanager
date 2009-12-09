<?php
// B.H.

abstract class html_form_elm extends html_element {

  public $type;
  public $name;
  public $param;
  public $default_value = null;
  
  public function __construct() {
    if (!empty($this->param['class'])) $this->attr['class'] = $this->param['class'];
    if (!empty($this->param['attr'])) {
        if (!is_array($this->param['attr'])) $this->param['attr'] = array($this->param['attr']);
        if (empty($this->attr)) $this->attr = array();
        $this->attr = array_merge($this->attr,$this->param['attr']);
    }
  }
  
  // true if this class is a submit button (triggers postback binding)
  abstract public function is_submit();

  /**
   * @desc returns a value that was submitted for the control
   * @param string $method
   * @return mixed
   */
  public function postback_value($method) {
    switch($method) {
    case 'get':
      return (isset($_GET[$this->name]))?stripslashes($_GET[$this->name]):$this->default_value;
    case 'post':
      return (isset($_POST[$this->name]))?stripslashes($_POST[$this->name]):$this->default_value;
    }
  }
  
  public function init($parent=null) {
    parent::init($parent);
    if (!empty($this->form_context)) $this->form_context->attach_control($this);
  }
  
  public function value() {
    if (empty($this->form_context)) throw new Exception("form_context not set");
    return isset($this->form_context->data[$this->name])?
      $this->form_context->data[$this->name]:null;
  }
  
  public function set_form_context($context) {
    if (!empty($this->form_context)) $this->form_context->detach_control($this); 
    parent::set_form_context($context);
    if (!empty($this->form_context)) $this->form_context->attach_control($this);
  }

}
?>