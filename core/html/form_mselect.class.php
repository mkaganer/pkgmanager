<?php
// B.H.

// $type='mselect'
/**
 * @desc type='mselect' multiple selection box
 * $param[0] - array('val'=>'text',...) - options
 * $param[1] - array('val1','val2') - default selected vaules (optional)
 * @author mkaganer
 *
 */
class html_form_mselect extends html_form_elm {

  public $values;
  
  /**
   * @var array a list of the selected values
   */
  public $mselect_default;
  
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
    $this->mselect_default = empty($param[1])?array():$param[1];
    if (!is_array($this->mselect_default)) $this->mselect_default = array($this->mselect_default);
    $this->default_value = $this->mselect_default;
    $this->param = $param;
    parent::__construct();
  }
  
  public function is_submit() {
    return false;
  }

  /**
   * @desc returns a value that was submitted for the control
   * @param string $method
   * @return mixed
   */
  public function postback_value($method) {
    switch($method) {
        case 'get':
            if (!isset($_GET[$this->name])) return $this->mselect_default;
            $vals = $_GET[$this->name];
            break;
        case 'post':
            if (!isset($_POST[$this->name])) return $this->mselect_default;
            $vals = $_POST[$this->name];
            break;
    }
    if (empty($vals)) return array();
    $idx = array_keys($vals);
    $all_values = array_keys($this->values);
    $vals = array();
    foreach ($idx as $i) $vals[] = $all_values[$i];
    return $vals;
  }

  protected function render() {
    $at = '';
    $vals = $this->value();
    
    $attr = $this->attr;
    foreach($attr as $atk => $atv) $at .= " ${atk}=\"".htmlspecialchars($atv)."\"";
    if ($this->type=='mselect') {
        $res = "<div${at}>";
        $id = "_id_".rand(1,1000000).rand(1,1000000).'_';
        $val_keys = array_keys($this->values);
        foreach($val_keys as $ii=>$key) {
            $s = (in_array($key,$vals,true))?' checked="checked"':'';
            $name = htmlspecialchars($this->name);
            $txt = htmlspecialchars($this->values[$key]);
            $res .= "<div class=\"mcheckbox\">".
                "<input type=\"checkbox\" name=\"${name}[$ii]\"${s} id=\"${id}${ii}\" value=\"1\"/>".
                "<label for=\"${id}${v}\">${txt}</label></div>";
        }
        $res .= "</div>";
        return $res;
    }
  }
  
}
