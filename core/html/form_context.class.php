<?php
// B.H.

/**
 * @author mkaganer
 * @desc used to bind form-related controls to data
 */
class html_form_context {

  public $data = array();
  public $controls = array();
  
  private $method;
  private $submits = array();

  public function __construct($method='post') {
    $this->set_method($method);
  }
  
  public function set_method($method) {
    switch($method) {
    case 'post':
    case 'get':
      break;
    default:
      throw new Exception("Bad submit method!");
    }
    $this->method = $method;
  }
  
  public function get_method() {
    return $this->method;
  }
  
  private function bymethod_isset($name) {
    switch($this->method) {
    case 'post':
      return isset($_POST[$name]);
    case 'get':
      return isset($_GET[$name]);
    }
  }
  
  // check if the control has already been attached
  private function is_attached($obj,$subj) {
    foreach($subj as $ctrl) {
      if (is_object($ctrl)&&($obj===$ctrl)) return true;
      if (is_array($ctrl)&&$this->is_attached($obj,$ctrl)) return true;
    }
  }
  
  public function attach_control(html_form_elm $obj) {
    if ($this->is_attached($obj,$this->controls)) return;
    $name = $obj->name;
    if ($obj instanceof html_form_radio) {
      $this->controls[$name][] = $obj;
    } else {
      if (isset($this->controls[$name])) throw new Exception("Duplicate name '$name'!");
      $this->controls[$name] = $obj;
    }
    if ($obj->is_submit()) $this->submits[] = $obj;
  }
  
  // TODO: needs some debuging...
  public function detach_control(html_form_elm $obj) {
    if (!$this->is_attached($obj,$this->controls)) return;
    $name = $obj->name;
    if (!is_array($this->controls[$name])) unset($this->controls[$name]);
    else {
      foreach($this->controls[$name] as $key => $ctrl)
        if ($obj===$ctrl) unset($this->controls[$name][$key]);
      if (empty($this->controls[$name])) unset($this->controls[$name]);
    }    
    foreach($this->submits as $key => $ctrl)
      if ($obj===$ctrl) unset($this->submits[$key]);
  }
  
  public function attach_submit($string) {
    if (empty($string)) throw new Exception("Invalid custom submit '$string'");
    $this->submits[] = $string;
  }
  
  // check if submit was fired and returns the fired submit name or false
  public function check_submit($name=null) {
    if (is_null($name)) {
      foreach($this->submits as $ctrl) {
        if (is_string($ctrl)) {
          if ($this->bymethod_isset($ctrl)) return $ctrl;
        } elseif ($ctrl->postback_value($this->method)) {
          return $ctrl->name;
        }
      }
      return false;
    } else {
      if (!isset($this->controls[$name])) {
        $ctrl = null;
        foreach($this->submits as $submit) if (is_string($submit)&&($submit==$name)) {
          $ctrl = $name;
          break;
        }
        if (empty($ctrl)) throw new Exception("No such control or submit '$name'");
        return $this->bymethod_isset($ctrl);
      }
      $ctrl = $this->controls[$name];
      if (!$ctrl->is_submit()) throw new Exception("'$name' is not submit!");
      return ($ctrl->postback_value($this->method))?$ctrl->name:false;
    }
  }

  // load control values submitted by the user into $this->data array
  public function load_data($with_submits=false) {
    foreach($this->controls as $name => $ctrl) {
      if ($ctrl instanceof html_form_elm) {
        if ((!$with_submits)&&$ctrl->is_submit()) continue;
        $this->data[$name] = $ctrl->postback_value($this->method);
      } elseif (is_array($ctrl)) {
        $this->data[$name] = null;
        foreach($ctrl as $elm) if ($elm->postback_value($this->method)) {
          $this->data[$name] = $elm->checked_value;
          break;
        }
      } else throw new Exception("Bad control reference!");
    }
  }
  
}