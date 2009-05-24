<?php
// B.H.

class html_form_checkbox extends html_form_elm {

  public $checked_value;

  public function __construct($type,$name,$param) {
    if (empty($type)||empty($name)) throw new Exception("Bad arguments!");
    $this->type = $type;
    $this->name = $name;
    $this->param = $param;
    $this->checked_value = isset($this->param[0])?$this->param[0]:'1';
    $this->prompt = @$this->param[1];
    parent::__construct();
  }
  
  public function is_submit() {
    return false;
  }

  public function postback_value($method) {
    switch($method) {
    case 'get':
      return (isset($_GET[$this->name]))?($_GET[$this->name]==$this->checked_value):false;
    case 'post':
      return (isset($_POST[$this->name]))?($_POST[$this->name]==$this->checked_value):false;
    }
  }
  
  protected function render() {
    $val = $this->value();
    $at = '';
    $attr = $this->attr;
    $attr['name'] = $this->name;
    $attr['value'] = $this->checked_value;
    if ($val) $attr['checked'] = 'checked';
    if ((!empty($this->prompt))&&(!isset($attr['id']))) {
      $attr['id'] = '_id_'.rand(0,1000000).rand(0,1000000);
    }
    foreach($attr as $atk => $atv) $at .= " ${atk}=\"".htmlspecialchars($atv)."\"";
    $res = "<input type=\"checkbox\"${at} />";
    if (!empty($this->prompt)) $res .= "<label for=\"$attr[id]\">{$this->prompt}</label>";
    return $res;
  }
  
}
?>