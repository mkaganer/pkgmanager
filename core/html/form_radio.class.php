<?php
// B.H.

class html_form_radio extends html_form_elm {

  // value associated with this radio button
  public $checked_value;

  public function __construct($type,$name,$param) {
    if (empty($name)) throw new Exception("Bad arguments!");
    $this->type = $type;
    $this->name = $name;
    if (empty($param[0])) throw new Exception("Empty checked value for radio button!");
    $this->checked_value = $param[0];
    $this->param = $param;
    $this->prompt = @$this->param[1];
    parent::__construct();
  }
  
  public function is_submit() {
    return false;
  }

  public function postback_value($method) {
    $ch_val = $this->checked_value;
    switch($method) {
    case 'get':
      return (isset($_GET[$this->name]))?($_GET[$this->name]==$ch_val):false;
    case 'post':
      return (isset($_POST[$this->name]))?($_POST[$this->name]==$ch_val):false;
    }
  }
  
  protected function render() {
    if (empty($this->form_context)) throw new Exception("form_context not set");
    $val = $this->postback_value($this->form_context->get_method());
    $at = '';
    $attr = $this->attr;
    $attr['name'] = $this->name;
    $attr['value'] = $this->checked_value;
    if ($val) $attr['checked'] = 'checked';
    if ((!empty($this->prompt))&&(!isset($attr['id']))) {
      $attr['id'] = '_id_'.rand(0,1000000).rand(0,1000000);
    }
    foreach($attr as $atk => $atv) $at .= " ${atk}=\"".htmlspecialchars($atv)."\"";
    $res = "<input type=\"radio\"${at} />";
    if (!empty($this->prompt)) $res .= "<label for=\"$attr[id]\">{$this->prompt}</label>";
    return $res;
  }
  
}
?>