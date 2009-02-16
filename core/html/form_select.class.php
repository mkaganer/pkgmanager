<?php
// B.H.

// $type='select','mradio'
class html_form_select extends html_form_elm {

  public $values;

  public function __construct($type,$name,$param) {
    if (empty($type)||empty($name)) throw new Exception("Bad arguments!");
    $this->type = $type;
    $this->name = $name;
    if (empty($param[0])) {
      $this->values = array();
    } else {
      $this->values = $param[0];
      unset($param[0]);
    }
    $this->param = $param;
    parent::__construct();
  }
  
  public function is_submit() {
    return false;
  }
  
  public static function options($arr,$sel) {
    $res = '';
    foreach($arr as $key => $val) {
      $s = ($key==$sel)?' selected="selected"':'';
      $res.="<option value=\"".htmlspecialchars($key)."\"$s>".htmlspecialchars($val)."</option>";
    }
    return $res;
  }

  protected function render() {
    $at = '';
    $val = $this->value();
    $attr = $this->attr;
    $attr['name'] = $this->name;
    foreach($attr as $atk => $atv) $at .= " ${atk}=\"".htmlspecialchars($atv)."\"";
    if ($this->type=='select') {
      $opt = self::options($this->values,$val);
      return "<select${at}>${opt}</select>";
    } elseif ($this->type=='mradio') {
      $res = '';
      $id = "_id_".rand(1,1000000).rand(1,1000000).'_';
      foreach($this->values as $key=>$txt) {
        $s = ($key==$val)?' checked="checked"':'';
        $name = htmlspecialchars($this->name);
        $v = htmlspecialchars($key);
        $txt = htmlspecialchars($txt);
        $res .= "<div class=\"mradio\"><input type=\"radio\" name=\"$name\"${s} id=\"${id}${v}\" value=\"$v\"/><label for=\"${id}${v}\">${txt}</label></div>";
      }
      return $res;
    }
  }
  
}
?>
