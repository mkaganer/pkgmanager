<?php
// B.H.

// $type='text'
class html_form_text extends html_form_elm {

  public function __construct($type,$name,$param) {
    if (empty($type)||empty($name)) throw new Exception("Bad arguments!");
    $this->type = $type;
    $this->name = $name;
    $this->param = $param;
    if (isset($this->param[0])) $this->attr['size'] = $this->param[0];
    if (isset($this->param['dir'])) $this->attr['dir'] = $this->param['dir'];
    parent::__construct();
  }
  
  public function is_submit() {
    return false;
  }

  protected function render() {
    $type = $this->type;
    $at = '';
    $val = $this->value();
    $attr = $this->attr;
    $attr['name'] = $this->name;
    if (!is_null($val)) $attr['value'] = $val;
    foreach($attr as $atk => $atv) $at .= " ${atk}=\"".htmlspecialchars($atv)."\"";
    return "<input type=\"${type}\"${at} />";
  }
  
}
?>