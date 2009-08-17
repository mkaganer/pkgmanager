<?php
// B.H.

class html_column {

  public $title;
  public $td_attr,$th_attr;
  
  public $td_attr_str = null;
  public $th_attr_str = null;

  public function __construct($title,$width=null,$align=null) {
    $this->title = $title;
    $attr = array();
    if (!empty($width)) $attr['width'] = $width;
    if (!empty($align)) $attr['align'] = $align;
    $this->td_attr = $attr;
    $this->th_attr = $attr;
    // temporary: by default, <th> is always centered (better to put in CSS....)
    $this->th_attr['align'] = 'center';
  }
  
  protected function make_attr_str($arr) {
    $at = '';
    foreach($arr as $atk => $atv) $at .= " ${atk}=\"".htmlspecialchars($atv)."\"";
    return $at;
  }
  
  public function format_header() {
    return htmlspecialchars($this->title);
  }
  public function format_data($val) {
    return $val;
  }
  
  public function render_header() {
    if (is_null($this->th_attr_str)) $this->th_attr_str = $this->make_attr_str($this->th_attr);
    return "<th{$this->th_attr_str}>".$this->format_header()."</th>";
  }

  public function render_data($line_num,$id,$val) {
    if (is_null($this->td_attr_str)) $this->td_attr_str = $this->make_attr_str($this->td_attr);
    $val_str = ($val instanceof html_block)?$val->get_html():((string)$val);
    return "<td{$this->td_attr_str}>".$this->format_data($val_str)."</td>";
  }
}