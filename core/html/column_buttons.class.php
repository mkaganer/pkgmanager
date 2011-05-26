<?php
// B.H.

class html_column_buttons extends html_column {

  public $btn_data,$id_name;

  public function __construct($title,$width=null,$align=null,$btn_data,$id_name='id') {
    parent::__construct($title,$width,$align);
    $this->btn_data = $btn_data;
    $this->id_name = $id_name;
  }
  
  public function format_data($val) {
    //return "<img src=\"$this->img\" alt=\"\" />";
    $res = '';
    foreach($this->btn_data as $btn) {
      $img = "<img src=\"$btn[0]\" alt=\"".htmlspecialchars($btn[1])."\" border=\"0\" class=\"html_column_button\" />";
      if (is_string($btn[2])) {
        $url = sprintf($btn[2],$val);
      } else {
        // btn[2] is html_url
        if (!empty($this->id_name)) $url = $btn[2]->get_url(array(($this->id_name)=>$val));
        else $url = $btn[2]->get_url();
      }
      $a_add = empty($btn[3])?'':" onclick=\"$btn[3]\"";
      $res .= "<a href=\"$url\"${a_add}>$img</a>";
    }
    return $res;
  }
}