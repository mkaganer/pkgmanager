<?php
// B.H.

// $type='textarea'
class html_form_textarea extends html_form_elm {

  // $param: cols,rows
  public function __construct($type,$name,$param) {
    if (empty($type)||empty($name)) throw new Exception("Bad arguments!");
    $this->type = $type;
    $this->name = $name;
    $this->param = $param;
    parent::__construct();
  }
  
  public function is_submit() {
    return false;
  }

  protected function render() {
    $at = '';
    $val = $this->value();
    $attr = $this->attr;
    $attr['name'] = $this->name;
    if (isset($this->param[0])) $attr['cols'] = $this->param[0];
    if (isset($this->param[1])) $attr['rows'] = $this->param[1];
    foreach($attr as $atk => $atv) $at .= " ${atk}=\"".htmlspecialchars($atv)."\"";
    return "<textarea${at}>".htmlspecialchars($val)."</textarea>";
  }
  
}
?>