<?php
// B.H.

class html_form_image extends html_form_elm {

  public function __construct($type,$name,$param) {
    if (empty($type)||empty($name)) throw new Exception("Bad arguments!");
    $this->type = $type;
    $this->name = $name;
    $this->param = $param;
    parent::__construct();
  }
  
  public function is_submit() {
    return true;
  }
  public function postback_value($method) {
    switch($method) {
    case 'get':
      return isset($_GET[$this->name]);
    case 'post':
      return isset($_POST[$this->name]);
    }
  }

  protected function render() {
    $at = '';
    $attr = $this->attr;
    $attr['name'] = $this->name;
    if (!isset($this->param[0])) throw new Exception("input type='image' must have src param");
    $attr['src'] = $this->param[0];
    $attr['value'] = isset($this->param[1])?$this->param[1]:"submit";
    $attr['type'] = 'image';
    foreach($attr as $atk => $atv) $at .= " ${atk}=\"".htmlspecialchars($atv)."\"";
    return "<input ${at} />";
  }
  
}
?>
